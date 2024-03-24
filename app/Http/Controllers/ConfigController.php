<?php

namespace App\Http\Controllers;

use App\Http\Traits\CommonTrait;
use App\Http\Traits\ConfigTrait;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redis;

class ConfigController extends Controller
{
    use ConfigTrait, CommonTrait;

    public function list(Request $request)
    {
        if ($request->ajax()) {

            $params = [
                'start'       => $request->start,
                'length'      => $request->length,
                'searchValue' => $request->search['value'],
            ];

            $data = $this->getConfigs($params);

            // Prepare response
            $response         = $data;
            $response['draw'] = intval($request->draw);

            return response()->json($response);
        }

        return view('config.list');
    }

    public function create()
    {
        return view('config.create');
    }

    public function store(Request $request)
    {
        // Validate the request...
        $validatedData = $request->validate([
            'given_name' => 'required|string|unique:configs,given_name',
            'source_url' => 'nullable|string|unique:configs,source_url',
        ]);

        $config = $this->storeConfig($validatedData);

        if ($config) {
            return redirect()
                ->route('config.list')
                ->with('success', 'Config created successfully!');
        }

        return redirect()
            ->route('config.create')
            ->with('error', 'Config creation failed!');
    }

    public function details($id)
    {
        return view('config.details', ['config' => Config::findOrFail($id)]);
    }

    public function destroy($id)
    {
        $deleted = $this->destroyConfig($id);

        if ($deleted) {
            return redirect()
                ->route('config.list')
                ->with('success', 'Configuration and associated files deleted successfully!');
        }

        return redirect()
            ->route('config.list')
            ->with('error', 'Failed to delete configuration!');
    }
}
