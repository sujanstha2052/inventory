<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with('group');

        if ($request->has('filter.group_id')) {
            $query->where('customer_group_id', $request->input('filter.group_id'));
        }
        if ($request->has('filter.is_active')) {
            $query->where('is_active', $request->boolean('filter.is_active'));
        }
        $sortField = $request->input('sort', 'name');
        $sortDirection = $request->input('direction', 'asc');
        $allowedSorts = ['name', 'code', 'outstanding_balance', 'total_sales'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $customers = $query->paginate($request->input('per_page', 15));

        return CustomerResource::collection($customers);
    }

    public function show(Customer $customer)
    {
        $customer->load('group', 'addresses');

        return new CustomerResource($customer);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers',
            'phone' => 'nullable|string|max:30',
            'company_name' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['code'] = 'CUST-'.now()->format('Y').'-'.str_pad(Customer::max('id') + 1, 4, '0', STR_PAD_LEFT);
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $customer = Customer::create($data);

        return new CustomerResource($customer->load('group'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|unique:customers,email,'.$customer->id,
            'phone' => 'nullable|string|max:30',
            'company_name' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['updated_by'] = auth()->id();
        $customer->update($data);

        return new CustomerResource($customer->fresh('group'));
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully']);
    }
}
