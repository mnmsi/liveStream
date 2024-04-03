<?php

namespace App\Http\Controllers;

use App\Http\Traits\CommonTrait;
use App\Http\Traits\ConfigTrait;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConfigController extends Controller
{
    use ConfigTrait, CommonTrait;

    public function listView(Request $request)
    {
        $url = route('config.getData');
        if (@file_get_contents($url) === false) {
            $url = Str::replace('http://', 'https://', $url);
        }

        return view('config.list', compact('url'));
    }

    public function getData(Request $request)
    {
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

    public function edit($id)
    {
        return view('config.edit', ['config' => Config::findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        // Validate the request...
        $validatedData = $request->validate([
            'given_name' => 'required|string|unique:configs,given_name,' . $id,
            'source_url' => 'nullable|string|unique:configs,source_url,' . $id,
        ]);

        $config = $this->updateConfig($id, $validatedData);

        if ($config) {
            return redirect()
                ->route('config.list')
                ->with('success', 'Config updated successfully!');
        }

        return redirect()
            ->route('config.edit', $id)
            ->with('error', 'Config update failed!');
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
