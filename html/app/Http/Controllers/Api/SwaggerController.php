<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         title="My API",
 *         version="1.0.0"
 *     ),
 *     @OA\Components(
 *         @OA\SecurityScheme(
 *             securityScheme="bearerAuth",
 *             type="http",
 *             scheme="bearer",
 *             bearerFormat="JWT"
 *         )
 *     )
 * )
 */
class SwaggerController
{
    // This class can be empty, it just holds the annotations.
}
