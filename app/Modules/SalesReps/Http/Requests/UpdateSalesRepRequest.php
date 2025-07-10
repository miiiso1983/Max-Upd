<?php

namespace App\Modules\SalesReps\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSalesRepRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // تم تعطيل التحقق من الصلاحيات مؤقتاً
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $salesRepId = $this->route('salesRep')->id ?? null;
        $userId = $this->route('salesRep')->user_id ?? null;

        return [
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => 'required|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'governorate' => 'nullable|string|max:100',
            'hire_date' => 'required|date',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'national_id' => 'nullable|string|max:50',
            'base_salary' => 'nullable|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'monthly_target' => 'nullable|numeric|min:0',
            'supervisor_id' => [
                'nullable',
                'exists:sales_representatives,id',
                Rule::notIn([$salesRepId]), // منع المندوب من أن يكون مشرفاً على نفسه
            ],
            'is_active' => 'boolean',
            'territory_ids' => 'nullable|array',
            'territory_ids.*' => 'exists:territories,id',
            'customer_ids' => 'nullable|array',
            'customer_ids.*' => 'exists:customers,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم المندوب مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.unique' => 'البريد الإلكتروني مستخدم مسبقاً',
            'phone.required' => 'رقم الهاتف مطلوب',
            'hire_date.required' => 'تاريخ التوظيف مطلوب',
            'hire_date.date' => 'تاريخ التوظيف غير صحيح',
            'birth_date.date' => 'تاريخ الميلاد غير صحيح',
            'gender.in' => 'الجنس يجب أن يكون ذكر أو أنثى',
            'base_salary.numeric' => 'الراتب الأساسي يجب أن يكون رقماً',
            'base_salary.min' => 'الراتب الأساسي لا يمكن أن يكون سالباً',
            'commission_rate.numeric' => 'نسبة العمولة يجب أن يكون رقماً',
            'commission_rate.min' => 'نسبة العمولة لا يمكن أن تكون سالبة',
            'commission_rate.max' => 'نسبة العمولة لا يمكن أن تزيد عن 100%',
            'monthly_target.numeric' => 'الهدف الشهري يجب أن يكون رقماً',
            'monthly_target.min' => 'الهدف الشهري لا يمكن أن يكون سالباً',
            'supervisor_id.exists' => 'المشرف المحدد غير موجود',
            'supervisor_id.not_in' => 'لا يمكن للمندوب أن يكون مشرفاً على نفسه',
            'territory_ids.array' => 'المناطق يجب أن تكون مصفوفة',
            'territory_ids.*.exists' => 'إحدى المناطق المحددة غير موجودة',
            'customer_ids.array' => 'العملاء يجب أن تكون مصفوفة',
            'customer_ids.*.exists' => 'أحد العملاء المحددين غير موجود',
        ];
    }
}
