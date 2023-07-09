<?php

namespace App\Http\Controllers\api;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetLoanRequest;
use App\Http\Requests\LoanApproveRequest;
use App\Http\Requests\LoanRepaymentRequest;
use App\Http\Requests\StoreLoanRequest;
use App\Http\Resources\LoanResource;
use App\Models\LoanDetails;
use App\Models\Loans;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoansController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetLoanRequest $request)
    {
        $loanData = Loans::query()->with('loanDetails')
            ->where('user_id', Auth::user()->id)
            ->where('id', $request->id)
            ->first();
        return ApiResponse::success([
            'loan' => new LoanResource($loanData),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLoanRequest $request)
    {
        $user = Auth::user();
        $addCredit = $this->_getAmountWithCredit($request->amount);
        $loan = new Loans();
        $loan->amount = $addCredit;
        $loan->repaid_amount = $request->amount;
        $loan->term = $request->term;
        $loan->user_id = $user->id;
        $loan->status = 'pending';
        if ($loan->save()) {
            $termAmount = $addCredit / $request->term;
            for ($i = 0; $i < $request->term; $i++) {
                $loanDetails = new LoanDetails();
                $loanDetails->loan_id = $loan->id;
                $loanDetails->repayment_amount = $termAmount;
                $loanDetails->status = 'pending';
                $loanDetails->repayment_time = date('Y-m-d', strtotime("+$i weeks"));
                $loanDetails->save();
            }
            $loanData = Loans::query()->where('id', $loan->id)->first();
            return ApiResponse::success([
                'loan' => new LoanResource($loanData),
            ]);

        }


    }

    /**
     * Display the specified resource.
     */
    public function show(Loans $loans)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Loans $loans)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Loans $loans)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Loans $loans)
    {
        //
    }

    /**
     * @param LoanApproveRequest $request
     * @return JsonResponse
     */
    public function approveLoan(LoanApproveRequest $request): JsonResponse
    {
        $user = Auth::user();
        if ($user->role != 'admin') {
            return ApiResponse::error(["Unauthorized", 401]);
        }
        $loan = Loans::query()->findOrFail($request->id);
        if ($loan->status == $request->status) {
            return ApiResponse::error(["Loan already $request->status"]);
        }
        $loan->status = $request->status;
        $loan->save();
        LoanDetails::query()
            ->where('loan_id', $request->id)
            ->whereNot('status', 'pending')
            ->update(['status' => 'pending']);
        return ApiResponse::success([
            'message' => "Loan " . $request->status . " successfully"
        ]);
    }

    /**
     * @param LoanRepaymentRequest $request
     * @return JsonResponse
     */
    public function loanRepayment(LoanRepaymentRequest $request): JsonResponse
    {
        $loan = Loans::query()
            ->where('user_id', Auth::user()->id)
            ->where('id', $request->id)
            ->first();
        if (empty($loan)) {
            return ApiResponse::error(["Unauthorized", 401]);
        }
        if ($loan->status == 'rejected') {
            return ApiResponse::error(["Loan request has been rejected, can not pay for this loan"]);
        }
        if ($loan->status == 'paid') {
            return ApiResponse::success([
                'message' => "Loan already paid",
                'loan' => new LoanResource($loan),
            ]);
        }
        $loanDetails = LoanDetails::query()->where('loan_id', $request->id)
            ->where('status', 'pending')->first();
        $response = $this->_madeRepayment($request, $loanDetails);
        if ($response['status'] === false) {
            return ApiResponse::error([$response['message']]);
        }
        $loan = Loans::query()->findOrFail($loan->id);
        return ApiResponse::success([
            'message' => "Repayment paid successfully",
            'loan' => new LoanResource($loan),
        ]);
    }


    /**
     * @param $request
     * @param $loanDetails
     * @return array
     */
    private function _madeRepayment($request, $loanDetails): array
    {
        if (round($request->amount, 2) < round($loanDetails->repayment_amount, 2)) {
            return [
                'status' => false,
                'message' => "Please enter valid amount"
            ];
        }
        if ($request->amonut = $loanDetails->repayment_amount) {
            LoanDetails::query()
                ->where('id', $loanDetails->id)
                ->update(['status' => 'paid', 'repayment_date' => Carbon::now()]);
        }
        $loan = Loans::query()->findOrFail($request->id);
        if (round($request->amount, 2) > round($loanDetails->repayment_amount, 2)) {
            $oustandingAmount = $request->amount - $loanDetails->repayment_amount;
            LoanDetails::query()
                ->where('id', $loanDetails->id)
                ->whereNot('status', 'pending')
                ->update(['status' => 'paid', 'repayment_date' => Carbon::now()]);

            $allTimePaidLoanDetails = LoanDetails::query()->where('loan_id', $request->id)
                ->where('status', 'paid');
            $paidTerms = $allTimePaidLoanDetails->count();
            if ($paidTerms < $loan->terms) {
                $paidAmount = $allTimePaidLoanDetails->sum('repayment_amount');
                $remainingTerms = $loan->term - $paidTerms;
                $remainingAmount = $loan->amount - ($paidAmount + $oustandingAmount);
                $remainingTermsAmount = $remainingAmount / $remainingTerms;
                if ($remainingTerms > 0) {
                    LoanDetails::query()
                        ->where('loan_id', $loan->id)
                        ->where('status', 'pending')
                        ->delete();
                    for ($i = 0; $i < $remainingTerms; $i++) {
                        $loanDetails = new LoanDetails();
                        $loanDetails->loan_id = $loan->id;
                        $loanDetails->repayment_amount = $remainingTermsAmount;
                        $loanDetails->status = 'pending';
                        $loanDetails->repayment_time = date('Y-m-d', strtotime("+$i weeks"));
                        $loanDetails->save();
                    }
                }
            }
        }
        $allTimePaidLoanDetails = LoanDetails::query()->where('loan_id', $request->id)
            ->where('status', 'paid');

        if ($allTimePaidLoanDetails->count() == $loan->terms) {
            $loan->status = 'paid';
            $loan->save();
            LoanDetails::query()
                ->where('loan_id', $request->id)
                ->whereNot('status', 'pending')
                ->update(['status' => 'paid']);
            return [
                'status' => true,
                'message' => "Loan paid successfully"
            ];
        }
        return [
            'status' => true,
            'message' => "Repayment paid successfully"
        ];
    }

    /**
     * @param $amount
     * @return void
     */
    private function _getAmountWithCredit($amount)
    {
        $credit = 0;
        $creditAmount = ($amount * ($credit / 100));
        return $amount + $creditAmount;
    }

}
