<?php

namespace App\Models;

use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="ContactMessage",
 *     type="object",
 *     title="Contact Message",
 *     description="Contact message model",
 *     required={"id", "name", "email", "message", "created_at", "updated_at"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="The ID of the contact message",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="The name of the person who sent the message",
 *         example="John Doe"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="The email of the person who sent the message",
 *         example="johndoe@example.com"
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="The message content",
 *         example="Hello, I would like to know more about your services."
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="The time when the message was created",
 *         example="2021-01-01T00:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="The time when the message was last updated",
 *         example="2021-01-01T00:00:00Z"
 *     )
 * )
 */

class ContactMessage extends Model
{
    use HasFactory , SoftDeletes;
    protected $fillable = ['name' , 'email' , 'phone' , 'message'] ;
    
}
