<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\CursorPaginator;

class ApiResponseResource extends JsonResource
{
    public static $wrap = null;
    public function toArray($request)
    {
        $success = $this->resource['success'];
        $data = $this->resource['data'] ?? [];
        $message = $this->resource['message'];

        $response = [
            'success' => $success,
        ];
        if (!empty($message)) {
            $response['message'] = $message;
        }

        if (is_array($data)) {
            foreach ($data as $key => $datum) {
                if (!empty($datum->resource) && ($datum->resource instanceof CursorPaginator)) {
                    $response[$key] = $datum;
                    $response['next_cursor'] = $datum->resource->nextCursor()?->encode();
                } else {
                    $response[$key] = $datum;
                }
            }
        }

        return $response;
    }
}
