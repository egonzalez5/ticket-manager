<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketStoreRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'priority_id' => ['required', 'integer', 'exists:priorities,id'],
            // Solo permitir SLAs activos (evita inconsistencias con tickets futuros).
            'sla_id' => ['nullable', 'integer', 'exists:slas,id,active,1'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
        ];
    }
}

