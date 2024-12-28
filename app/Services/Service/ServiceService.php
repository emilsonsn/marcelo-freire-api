<?php

namespace App\Services\Service;

use App\Models\Service;
use Exception;
use Illuminate\Support\Facades\Validator;

class ServiceService
{
    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term;

            $services = Service::orderBy('id', 'desc');

            if (isset($search_term)) {
                $services->where('title', 'LIKE', "%{$search_term}%")
                    ->orWhere('description', 'LIKE', "%{$search_term}%");
            }

            if($request->filled('status')){
                $services->where('status', $request->status);
            }

            $services = $services->paginate($perPage);

            return $services;
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function create($request)
    {
        try {
            $rules = [
                'client_id' => 'required|exists:clients,id',
                'title' => 'required|string|max:255',
                'status' => 'nullable|in:Pending,Deliver',
                'description' => 'nullable|string',
                'users' => 'nullable|array',
                'users.*' => 'exists:users,id',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];
            }

            $service = Service::create($validator->validated());

            if($request->filled('users')){
                $service->users()->sync($request->users);
            }

            return ['status' => true, 'data' => $service];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function update($request, $service_id)
    {
        try {
            $rules = [
                'client_id' => 'required|exists:clients,id',
                'title' => 'required|string|max:255',
                'status' => 'nullable|in:Pending,Deliver',
                'description' => 'nullable|string',
                'users' => 'nullable|array',
                'users.*' => 'exists:users,id',
            ];
    
            $validator = Validator::make($request->all(), $rules);
    
            if ($validator->fails()) throw new Exception($validator->errors());
    
            $serviceToUpdate = Service::find($service_id);
    
            if (!isset($serviceToUpdate)) throw new Exception('Serviço não encontrado');
    
            $serviceToUpdate->update($validator->validated());
    
            if ($request->has('users')) {
                $serviceToUpdate
                    ->users()
                    ->sync($request->users);
            }
    
            return ['status' => true, 'data' => $serviceToUpdate];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
    
    public function delete($id)
    {
        try {
            $service = Service::find($id);

            if (!$service) throw new Exception('Serviço não encontrado');

            $serviceTitle = $service->title;
            $service->delete();

            return ['status' => true, 'data' => $serviceTitle];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function removeUserFromService($service_id, $user_id)
    {
        try {
            $service = Service::find($service_id);

            if (!$service) throw new Exception('Serviço não encontrado');

            if (!$service->users()->where('user_id', $user_id)->exists()) {
                throw new Exception('Usuário não vinculado a este serviço');
            }

            $service->users()->detach($user_id);

            return ['status' => true, 'message' => 'Usuário removido do serviço com sucesso'];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}