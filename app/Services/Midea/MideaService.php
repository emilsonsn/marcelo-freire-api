<?php

namespace App\Services\Midea;

use App\Models\Midea;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MideaService
{
    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term;

            $midias = Midea::orderBy('id', 'desc');

            if (isset($search_term)) {
                $midias->where('description', 'LIKE', "%{$search_term}%");
            }

            $midias = $midias->paginate($perPage);

            return $midias;
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function create($request)
    {
        try {
            $rules = [                
                'service_id' => ['required', 'integer', 'exists:services,id'],
                'description' => ['nullable', 'string'],
                'midia' => ['required', 'file'],
            ];
    
            $validator = Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];
            }
    
            $data = $validator->validated();
            $data['user_id'] = Auth::user()->id;
    
            if ($request->hasFile('midia')) {
                $path = $request->file('midia')->store('public/midias');
                $data['path'] = str_replace('public/', '', $path);
            } else {
                throw new Exception('Falha ao salvar o arquivo de mídia.');
            }
    
            $midia = Midea::create($data);
    
            return ['status' => true, 'data' => $midia];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
    
    public function update($request, $midia_id)
    {
        try {
            $rules = [                
                'service_id' => ['required', 'integer', 'exists:services,id'],
                'description' => ['nullable', 'string'],
                'midia' => ['required', 'file'],
            ];
    
            $validator = Validator::make($request->all(), $rules);
    
            if ($validator->fails()) throw new Exception($validator->errors());
    
            $midiaToUpdate = Midea::find($midia_id);
    
            if (!isset($midiaToUpdate)) throw new Exception('Mídia não encontrada');

            $data = $validator->validated();
            $data['user_id'] = Auth::user()->id;
    
            if ($request->hasFile('midia')) {
                $path = $request->file('midia')->store('public/midias');
                $data['path'] = str_replace('public/', '', $path);
            } else {
                throw new Exception('Falha ao salvar o arquivo de mídia.');
            }
    
            $midiaToUpdate->update($data);
    
            return ['status' => true, 'data' => $midiaToUpdate];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
    
    public function delete($id)
    {
        try {
            $midia = Midea::find($id);

            if (!$midia) throw new Exception('Mídia não encontrada');

            $midiaDescription = $midia->description;
            $midia->delete();

            return ['status' => true, 'data' => $midiaDescription];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}