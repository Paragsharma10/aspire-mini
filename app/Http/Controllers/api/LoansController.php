<?php

namespace App\Http\Controllers\api;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetLoanRequest;
use App\Http\Requests\LoanApproveRequest;
use App\Http\Requests\LoanRepaymentRequest;
use App\Http\Requests\StoreLoanRequest;
use App\Http\Resources\LoanResource;
use App\Http\Resources\UserResource;
use App\Models\LoanDetails;
use App\Models\Loans;
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
        $loan = new Loans();
        $loan->amount = $request->amount;
        $loan->term = $request->term;
        $loan->user_id = $user->id;
        $loan->status = 'pending';
        if ($loan->save()) {
            $termAmount = $request->amount / $request->term;
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
        $loan = Loans::query()->findOrFail( $request->id);
        if ($loan->status ==  $request->status) {
            return ApiResponse::error(["Loan already $request->status"]);
        }
        $loan->status = $request->status;
        $loan->save();
        LoanDetails::query()
            ->where('loan_id', $request->id)
            ->whereNot('status', 'pending')
            ->update(['status' => 'pending']);
        return ApiResponse::success([
            'message' => "Loan ".$request->status." successfully"
        ]);
    }

    /**
     * @param LoanRepaymentRequest $request
     * @return JsonResponse
     */
    public function loanRepayment(LoanRepaymentRequest $request)
    {
        $loan = Loans::query()
            ->where('user_id', Auth::user()->id)
            ->where('id', $request->id)
            ->first();
        if (empty($loan)) {
            return ApiResponse::error(["Unauthorized", 401]);
        }
        if ($loan->status == 'rejected') {
            return ApiResponse::error(["can not pay for this loan"]);
        }
        $loanDetails = LoanDetails::query()->where('loan_id', $request->id)
            ->where('status', 'pending')->first();
    }
}
