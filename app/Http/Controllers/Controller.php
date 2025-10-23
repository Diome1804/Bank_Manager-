<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         title="Bank Manager API",
 *         version="1.0.0",
 *         description="API documentation for Bank Manager application"
 *     ),
 *     @OA\Server(
 *         url=L5_SWAGGER_CONST_HOST,
 *         description="API server"
 *     )
 * )
 * @OA\PathItem(path="/")
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
