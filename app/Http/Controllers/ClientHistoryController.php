<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Services\ClientHistoryService;
use Illuminate\Support\Facades\Auth;

class ClientHistoryController extends Controller
{
    /**
     * Show client history page
     */
    public function show($clientId)
    {
        $cliente = Cliente::findOrFail($clientId);

        // Verify permission - only admin, consultor assigned to this client, or financeiro can view
        $user = Auth::user();
        if ($user->papel === 'consultor' && $cliente->consultor_id !== $user->id) {
            abort(403, 'Você não tem permissão para ver o histórico deste cliente.');
        }

        $historyService = new ClientHistoryService($cliente);

        $data = [
            'cliente' => $cliente,
            'timeline' => $historyService->getTimeline(),
            'totalByPeriod' => $historyService->getTotalByPeriod(),
            'servicePatterns' => $historyService->getServicePatterns(),
            'suggestions' => $historyService->getSuggestions(),
            'overview' => $historyService->getOverview(),
        ];

        return view('cliente.historico', $data);
    }

    /**
     * API endpoint for timeline data
     */
    public function timelineJson($clientId)
    {
        $cliente = Cliente::findOrFail($clientId);

        $historyService = new ClientHistoryService($cliente);
        $timeline = $historyService->getTimeline();

        return response()->json([
            'success' => true,
            'data' => $timeline,
        ]);
    }

    /**
     * API endpoint for spent by period
     */
    public function spentByPeriodJson($clientId)
    {
        $cliente = Cliente::findOrFail($clientId);

        $historyService = new ClientHistoryService($cliente);
        $totalByPeriod = $historyService->getTotalByPeriod();

        return response()->json([
            'success' => true,
            'data' => $totalByPeriod,
        ]);
    }

    /**
     * API endpoint for service patterns
     */
    public function patternsjson($clientId)
    {
        $cliente = Cliente::findOrFail($clientId);

        $historyService = new ClientHistoryService($cliente);
        $patterns = $historyService->getServicePatterns();

        return response()->json([
            'success' => true,
            'data' => $patterns,
        ]);
    }

    /**
     * API endpoint for suggestions
     */
    public function suggestionsJson($clientId)
    {
        $cliente = Cliente::findOrFail($clientId);

        $historyService = new ClientHistoryService($cliente);
        $suggestions = $historyService->getSuggestions();

        return response()->json([
            'success' => true,
            'data' => $suggestions,
        ]);
    }

    /**
     * API endpoint for overview statistics
     */
    public function overviewJson($clientId)
    {
        $cliente = Cliente::findOrFail($clientId);

        $historyService = new ClientHistoryService($cliente);
        $overview = $historyService->getOverview();

        return response()->json([
            'success' => true,
            'data' => $overview,
        ]);
    }
}
