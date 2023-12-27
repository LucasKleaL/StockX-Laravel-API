<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Error;
use Exception;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $database;

    public function __construct()
    {
        $this->database = app('firebase.firestore');
    }

    public function __invoke() {}

    public function getAll(): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->database
            ->database()
            ->collection('Products')
            ->where('deleted', '==', null)
            ->documents();

            if ($data->isEmpty()) {
                return collect();
            }

            $products = [];
            foreach ($data as $documentSnapshot) {
                $documentData = $documentSnapshot->data();
                $documentData['uid'] = $documentSnapshot->id();
                $products[] = $documentData;
            }

            return response()->json($products);
        } catch (Error $e) {
            return response()->json(['statusCode' => 500, 'error' => 'Failed to get all products: ' . $e->getMessage()], 500);
        }
    }

    public function get(Request $request, string $uid): \Illuminate\Http\JsonResponse
    {
        try {
            $data = [];
            $snapshot = $this->database
                ->database()
                ->collection('Products')
                ->document($uid)
                ->snapshot();

            if ($snapshot->exists()) {
                $data = $snapshot->data();
                $data['uid'] = $snapshot->id();
            }
            
            if (!empty($data) && $data['deleted'] === null) {
                return response()->json(['statusCode' => 200, 'product' => $data]);
            } else {
                return response()->json(['statusCode' => 404, 'product' => []], 404);
            }
        } catch (Exception $e) {
            return response()->json(['statusCode' => 500, 'error' => 'Failed to get product by UID: ' . $e->getMessage()], 500);
        }
    }

    public function add(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'amount' => 'required',
            'amountType' => 'required',
            'price' => 'required',
            'category' => 'required',
        ]);

        try {
            $documentReference = $this->database
            ->database()
            ->collection('Products')
            ->newDocument();

            $documentReference->set([
                'name' => $request->name,
                'amount' => $request->amount,
                'amountType' => $request->amountType,
                'price' => $request->price,
                'category' => $request->category,
                'created' => date('d/m/Y, H:m:i'),
                'modified' => null,
                'deleted' => null,
            ]);

            return response()->json(['statusCode' => 201, 'result' => $documentReference->id()], 201);
        } catch (Exception $e) {
            return response()->json(['statusCode' => 500, 'error' => 'Failed to update product: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $uid): \Illuminate\Http\JsonResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'amount' => 'required',
            'amountType' => 'required',
            'price' => 'required',
            'category' => 'required',
        ]);

        try {
            $documentReference = $this->database
                ->database()
                ->collection('Products')
                ->document($uid);

            if ($documentReference->snapshot()->exists()) {
                $documentReference->update([
                    ['path' => 'name', 'value' => $request->name],
                    ['path' => 'amount', 'value' => $request->amount],
                    ['path' => 'amountType', 'value' => $request->amountType],
                    ['path' => 'price', 'value' => $request->price],
                    ['path' => 'category', 'value' => $request->category],
                    ['path' => 'modified', 'value' => date('d/m/Y, H:m:i')],
                ]);
    
                return response()->json(['statusCode' => 201, 'result' => $documentReference->id()], 201);
            } else {
                return response()->json(['statusCode' => 404, 'error' => 'Product not found.'], 404);
            }
        } catch (Exception $e) {
            return response()->json(['statusCode' => 500, 'error' => 'Failed to update product: ' . $e->getMessage()], 500);
        }
    }

    public function delete(Request $request, string $uid): \Illuminate\Http\JsonResponse
    {
        try {
            $documentReference = $this->database
                ->database()
                ->collection('Products')
                ->document($uid);

            if ($documentReference->snapshot()->exists()) {
                $documentReference->update([
                    ['path' => 'deleted', 'value' => date('d/m/Y, H:m:i')],
                ]);
    
                return response()->json(['statusCode' => 200, 'result' => $documentReference->id()], 200);
            } else {
                return response()->json(['statusCode' => 404, 'error' => 'Product not found.'], 404);
            }
        } catch (Exception $e) {
            return response()->json(['statusCode' => 500, 'error' => 'Failed to update product: ' . $e->getMessage()], 500);
        }
    }
}
