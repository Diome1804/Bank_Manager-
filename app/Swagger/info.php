<?php

/**
 * @OA\Info(
 *     title="Bank Manager API - Abdoulaye Diome",
 *     description="Documentation de l'API du projet Bank Manager développée par Abdoulaye Diome.",
 *     version="1.0.0",
 *     @OA\Contact(
 *         name="Abdoulaye Diome",
 *         url="https://github.com/Diome1804/Bank_Manager-.git"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8080/api",
 *     description="Serveur de développement"
 * )
 *
 * @OA\Server(
 *     url="https://bank-manager-v6a9.onrender.com/api",
 *     description="Serveur de production"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="JWT Authorization header using the Bearer scheme. Example: 'Authorization: Bearer {token}'"
 * )
 */