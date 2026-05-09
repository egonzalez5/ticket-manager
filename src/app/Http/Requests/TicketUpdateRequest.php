<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // La ruta ya va protegida por middleware `auth`; aquí dejamos
        // la autorización como positiva para no cambiar el comportamiento.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string|array<mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'priority_id' => ['sometimes', 'integer', 'exists:priorities,id'],
            'status_id' => ['sometimes', 'integer', 'exists:ticket_statuses,id'],
            'sla_id' => ['sometimes', 'nullable', 'integer', 'exists:slas,id,active,1'],

            // Permite actualizar tags si la UI los envía.
            'tags' => ['sometimes', 'nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
        ];
    }
}

