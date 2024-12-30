<?php

namespace App\Services\Midea;

use App\Mail\CommentMail;
use App\Models\Comment;
use App\Models\Midea;
use App\Models\ServiceCode;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class MideaService
{
    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term;
            $service_id = $request->service_id ?? null;

            $mideas = Midea::with('user', 'comments')
                ->orderBy('id', 'desc');

            if (isset($search_term)) {
                $mideas->where('description', 'LIKE', "%{$search_term}%");
            }

            if(isset($service_id)){
                $mideas->where('service_id', $service_id);
            }

            $mideas = $mideas->paginate($perPage);

            return $mideas;
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function getByCode($code){
        try {

            $serviceCode = ServiceCode::where('code', $code)
                ->whereBetween('created_at', [Carbon::now()->subDay(7), Carbon::now()])
                ->first();

            if(!isset($serviceCode)){
                throw new Exception('Código inválido ou expirado');
            }

            $mideas = Midea::with('comments')->where('service_id', $serviceCode->service_id)
                ->get();  

            return [
                'status' => true,
                'data' => $mideas
            ];

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
                'mideas' => ['required', 'array'],
                'mideas.*' => ['file']
            ];

            $requestData = $request->all();
    
            $validator = Validator::make($requestData, $rules);
    
            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];
            }
    
            $data = $validator->validated();
            $data['user_id'] = Auth::user()->id;

            $mideas = [];
            foreach($request->mideas as $midia){                
                $path = $midia->store('public/midias');
                $data['path'] = str_replace('public/', '', $path);

                $mideas[] = Midea::create($data);
            }            
        
            return [
                'status' => true,
                'data' => $mideas
            ];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function addComment($request)
    {
        try {
            $rules = [                
                'midea_id' => ['required', 'integer', 'exists:mideas,id'],
                'comment' => ['nullable', 'string'],                
            ];

            $requestData = $request->all();
    
            $validator = Validator::make($requestData, $rules);
    
            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];
            }            
    
            $data = $validator->validated();
            
            $comment = Comment::create($data);

            $client = $comment->midea->service->client;

            $admin = User::where('role', 'Admin')
                ->first();

            Mail::to($admin->email)
            ->send(new CommentMail(
                $client->name,
                $comment->comment
            ));
        
            return [
                'status' => true,
                'data' => $comment
            ];
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