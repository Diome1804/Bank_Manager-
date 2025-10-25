<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         title="Bank Manager API - Abdoulaye Diome",
 *         version="1.0.0",
 *         description="Documentation de l'API du projet Bank Manager développée par Abdoulaye Diome.",
 *         @OA\Contact(
 *             name="Abdoulaye Diome",
 *             url="https://github.com/Diome1804/Bank_Manager-.git"
 *         ),
 *         @OA\License(
 *             name="MIT",
 *             url="https://opensource.org/licenses/MIT"
 *         )
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
