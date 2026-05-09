<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition(): array
    {
        $mimes = [
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg',
            'png'  => 'image/png',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'txt'  => 'text/plain',
        ];

        $ext      = fake()->randomElement(array_keys($mimes));
        $mimeType = $mimes[$ext];
        $filename = fake()->word() . '_' . fake()->numberBetween(1, 999) . '.' . $ext;

        return [
            'ticket_id'  => Ticket::factory(),
            'message_id' => null,
            'file_name'  => $filename,
            'file_path'  => fn(array $attrs) => 'attachments/' . ($attrs['ticket_id'] ?? 0) . '/' . Str::uuid() . '.' . $ext,
            'mime_type'  => $mimeType,
            'file_size'  => fake()->numberBetween(10 * 1024, 5 * 1024 * 1024),
        ];
    }
}
