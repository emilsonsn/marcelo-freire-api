<?php

namespace App\Services\Client;

use App\Models\Client;
use Exception;
use Illuminate\Support\Facades\Validator;

class ClientService
{

    public function all()
    {
        try {
            
            $clients = Client::orderBy('id', 'desc')
                ->get();

            return [
                'status' => true,
                'data'   => $clients
            ];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }


    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term;

            $clients = Client::orderBy('id', 'desc');

            if(isset($search_term)){
                $clients->where('name', 'LIKE', "%{$search_term}%")
                    ->orWhere('cpf_cnpj', 'LIKE', "%{$search_term}%")
                    ->orWhere('email', 'LIKE', "%{$search_term}%")
                    ->orWhere('phone', 'LIKE', "%{$search_term}%");
            }

            $clients = $clients->paginate($perPage);

            return $clients;
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function create($request)
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'surname' => 'nullable|string|max:255',
                'email' => 'required|string|max:255',
                'url' => 'nullable|string|max:255',
                'gender' => 'nullable|in:Male,Female',
                'cpf_cnpj' => 'required|string|max:255',
                'phone' => 'required|string|max:255',
                'is_active' => 'nullable|boolean',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];;
            }

            $data = $validator->validated();

            $data['is_active'] = $data['is_active'] ? true : false;

            $client = Client::create($data);

            return ['status' => true, 'data' => $client];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function update($request, $user_id)
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'surname' => 'nullable|string|max:255',
                'email' => 'required|string|max:255',
                'url' => 'nullable|string|max:255',
                'gender' => 'nullable|in:Male,Female',
                'cpf_cnpj' => 'required|string|max:255',
                'phone' => 'required|string|max:255',
                'is_active' => 'nullable|boolean|max:255',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) throw new Exception($validator->errors());

            $clientToUpdate = Client::find($user_id);

            if(!isset($clientToUpdate)) throw new Exception('Cliente não encontrado');

            $clientToUpdate->update($validator->validated());

            return ['status' => true, 'data' => $clientToUpdate];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function delete($id){
        try{
            $client = Client::find($id);

            if(!$client) throw new Exception('Cliente não encontrado');

            $clientName = $client->name;
            $client->delete();

            return ['status' => true, 'data' => $clientName];
        }catch(Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}