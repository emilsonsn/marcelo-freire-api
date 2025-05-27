<?php

namespace App\Services\Midea;

use App\Mail\CommentMail;
use App\Models\Comment;
use App\Models\Midea;
use App\Models\Service;
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
            $perPage = $request->input('take', 100);
            $search_term = $request->search_term;
            $order_by = $request->order_by ?? 'id';
            $order = $request->order ?? 'DESC';
            $parent_id = $request->input('parent_id');
            $service_id = $request->input('service_id');

            $mideas = Midea::with('user', 'comments')
                ->orderBy($order_by, $order);

            if ($search_term) {
                $mideas->where('description', 'LIKE', "%{$search_term}%");
            }

            if ($service_id) {
                $mideas->where('service_id', $service_id);
            }

            if (! in_array($parent_id, [null, 'null'])) {
                $mideas->where('parent_id', $parent_id);
            } else {
                $mideas->whereNull('parent_id');
            }

            return $mideas->paginate($perPage);
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function getByCode($code)
    {
        try {
            $serviceCode = ServiceCode::where('code', $code)
                ->whereBetween('created_at', [Carbon::now()->subDay(7), Carbon::now()])
                ->first();

            if (!$serviceCode) {
                throw new Exception('Código inválido ou expirado');
            }

            $mideas = Midea::with('comments')
                ->where('service_id', $serviceCode->service_id)
                ->get();

            return ['status' => true, 'data' => $mideas];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function downloadOne($midea_id)
    {
        try {
            $midea = Midea::find($midea_id);

            if (!$midea) {
                throw new Exception('Mídia não encontrada');
            }

            $path = explode('storage/', $midea->path)[1];
            $filePath = storage_path('app/public/' . $path);

            if (!file_exists($filePath)) {
                throw new Exception('Arquivo não encontrado no servidor');
            }

            $fileName = basename($filePath);

            return response()->download($filePath, $fileName)->deleteFileAfterSend(false);
        } catch (Exception $error) {
            return response()->json(['status' => false, 'error' => $error->getMessage()], 400);
        }
    }

    public function download($code)
    {
        try {
            $serviceCode = ServiceCode::where(function ($query) use ($code) {
                    $query->where('code', $code)
                        ->orWhere('service_id', $code);
                })
                ->whereBetween('created_at', [Carbon::now()->subDay(7), Carbon::now()])
                ->first();

            $service = Service::find($code);

            if (!$serviceCode && !$service) {
                throw new Exception('Código inválido ou expirado');
            }

            $service_id = $serviceCode->service_id ?? $service->id;

            $mideas = Midea::where('service_id', $service_id)
                ->where('type', 'media')
                ->get();

            if ($mideas->isEmpty()) {
                throw new Exception('Nenhuma mídia encontrada para o serviço');
            }

            $zipFileName = 'mideas-' . now()->format('Y-m-d-H-i-s') . '.zip';
            $tempPath = storage_path('app/public/temp/' . $zipFileName);

            if (!file_exists(storage_path('app/public/temp'))) {
                mkdir(storage_path('app/public/temp'), 0777, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new Exception('Não foi possível criar o arquivo ZIP');
            }

            foreach ($mideas as $midea) {
                $filePath = public_path(parse_url($midea->path, PHP_URL_PATH));
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, basename($filePath));
                }
            }

            $zip->close();

            return response()->download($tempPath, $zipFileName)->deleteFileAfterSend(true);
        } catch (Exception $error) {
            return response()->json(['status' => false, 'error' => $error->getMessage()], 400);
        }
    }

    public function create($request)
    {
        try {
            $rules = [
                'type' => ['required', 'in:folder,media'],
                'description' => ['nullable', 'string'],
                'parent_id' => ['nullable', 'integer', 'exists:mideas,id'],
                'service_id' => ['nullable', 'integer', 'exists:services,id'],
                'mideas' => ['required_if:type,media', 'array'],
                'mideas.*' => ['file']
            ];

            $data = $request->all();
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];
            }

            $validated = $validator->validated();
            $validated['user_id'] = Auth::user()->id;

            if ($validated['type'] === 'folder') {
                $folder = Midea::create($validated);
                return ['status' => true, 'data' => [$folder]];
            }

            $result = [];
            foreach ($request->mideas as $file) {
                $path = $file->store('public/midias');
                $result[] = Midea::create([
                    'user_id' => $validated['user_id'],
                    'service_id' => $validated['service_id'],
                    'parent_id' => $validated['parent_id'] ?? null,
                    'description' => $file->getClientOriginalName(),
                    'path' => str_replace('public/', '', $path),
                    'type' => 'media',
                    'media_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ]);
            }

            return ['status' => true, 'data' => $result];
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

            $data = $request->all();
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];
            }

            $validated = $validator->validated();

            $comment = Comment::create($validated);

            $client = $comment->midea->service->client;
            $admin = User::where('role', 'Admin')->first();

            Mail::to($admin->email)->send(new CommentMail($client->name, $comment->comment));

            return ['status' => true, 'data' => $comment];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function update($request, $midia_id)
    {
        try {
            $rules = [
                'service_id' => ['required', 'integer', 'exists:services,id'],
                'parent_id' => ['nullable', 'integer', 'exists:mideas,id'],
                'description' => ['nullable', 'string'],
                'midia' => ['nullable', 'file'],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            $midiaToUpdate = Midea::find($midia_id);

            if (!$midiaToUpdate) {
                throw new Exception('Mídia não encontrada');
            }

            $data = $validator->validated();
            $data['user_id'] = Auth::user()->id;

            if ($request->hasFile('midia')) {
                $path = $request->file('midia')->store('public/midias');
                $data['path'] = str_replace('public/', '', $path);
                $data['media_type'] = $request->file('midia')->getClientMimeType();
                $data['size'] = $request->file('midia')->getSize();
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

            if (!$midia) {
                throw new Exception('Mídia não encontrada');
            }

            $midiaDescription = $midia->description;
            $midia->delete();

            return ['status' => true, 'data' => $midiaDescription];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}
