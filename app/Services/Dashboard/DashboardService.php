<?php

namespace App\Services\Dashboard;

use App\Models\Client;
use App\Models\Midea;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DashboardService
{
    public function cards()
    {
        try {
            $totalUsers = User::where('role', 'Manager')
                ->count();
            
            $totalClients = Client::count();

            $totalServices = Service::when(! auth()->user()->isAdmin(), function ($query) {
                    $query->whereHas('users', function ($q) {
                        $q->where('user_id', auth()->id());
                    });
                })
                ->whereMonth('created_at', Carbon::now()->month)
                ->count();
          
            $data = [
                'totalUsers' => $totalUsers,
                'totalClients' => $totalClients,
                'totalServices' => $totalServices,
            ];
           
            return [
                'status' => true,
                'data' => $data
            ];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function graphic()
    {
        try {
            $currentYear = date('Y');
    
            // Define o locale para exibir o mês em português
            setlocale(LC_TIME, 'pt_BR.UTF-8');
    
            $data = Service::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
                ->whereYear('created_at', $currentYear)
                ->groupByRaw('MONTH(created_at)')
                ->orderByRaw('MONTH(created_at)')
                ->get()
                ->map(function ($item) {
                    // Converte o número do mês para o nome do mês em português
                    $monthName = ucfirst(strftime('%B', mktime(0, 0, 0, $item->month, 10)));
                    return [
                        'month' => $monthName,
                        'total' => $item->total,
                    ];
                });
    
            return [
                'status' => true,
                'data' => $data
            ];
        } catch (Exception $error) {
            return [
                'status' => false,
                'error' => $error->getMessage(),
                'statusCode' => 400
            ];
        }
    }
    
    
}