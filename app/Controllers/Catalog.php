<?php

namespace App\Controllers;

class Catalog extends BaseController
{
    /**
     * GET /catalog/export
     * Exports catalog in JSON format for synchronization
     */
    public function export()
    {
        // Check for full sync header
        $fullSync = $this->request->getHeaderLine('X-Full-Sync') === 'true';

        $sourcePath = FCPATH . '../rogen.json';
        if (!is_file($sourcePath)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Source catalog file not found',
            ], 500);
        }

        $raw = file_get_contents($sourcePath);
        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['konex-import'])) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid source catalog format',
            ], 500);
        }

        // Full export: return source as-is (update created-at)
        if ($fullSync) {
            $data['konex-import']['metadata']['created-at'] = date('c');
            return $this->jsonResponse($data);
        }

        // Incremental: pick 10 random products and mark them as updated
        $ki = $data['konex-import'];
        $products = $ki['products'] ?? [];
        if (empty($products)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'No products available in source catalog',
            ], 500);
        }

        $count = min(10, count($products));
        // Get random unique indexes
        $indexes = array_rand($products, $count);
        if (!is_array($indexes)) {
            $indexes = [$indexes];
        }

        $timestamp = date('c');
        $updatedProducts = [];
        foreach ($indexes as $idx) {
            $p = $products[$idx];

            // 1) Ensure product id will not clash: prefix with external series
            if (isset($p['id'])) {
                $p['id'] = 'EXT-' . (string)$p['id'];
            }

            // 2) Randomize price within ±10% of original if present
            if (isset($p['price'])) {
                $origPrice = (float)$p['price'];
                $factor = mt_rand(90, 110) / 100; // 0.90 - 1.10
                $newPrice = round($origPrice * $factor, 2);
                $p['price'] = $newPrice;

                // 3) Cost is always 40% less than price
                $p['cost'] = round($newPrice * 0.6, 2);
            }

            // 4) Append Updated + timestamp to name
            if (isset($p['name']) && is_string($p['name'])) {
                $p['name'] = $p['name'] . ' (Updated ' . $timestamp . ')';
            } elseif (isset($p['name']) && is_array($p['name'])) {
                // If multilingual, update all locales
                foreach ($p['name'] as $k => $v) {
                    if (is_string($v)) {
                        $p['name'][$k] = $v . ' (Updated ' . $timestamp . ')';
                    }
                }
            }

            $updatedProducts[] = $p;
        }

        // Build incremental response preserving structure
        $ki['metadata']['created-at'] = $timestamp;
        $ki['metadata']['description'] = 'Incremental update with random 10 products';
        $ki['metadata']['total-products'] = count($updatedProducts);
        $ki['products'] = $updatedProducts;

        // Always include configurable-metadatas and configurable-products in delta sync
        $ki['configurable-metadatas'] = $data['konex-import']['configurable-metadatas'] ?? [];
        $ki['configurable-products'] = $data['konex-import']['configurable-products'] ?? [];
        
        // Update metadata counts
        $ki['metadata']['total-configurable-metadatas'] = count($ki['configurable-metadatas']);
        $ki['metadata']['total-configurable-products'] = count($ki['configurable-products']);

        return $this->jsonResponse(['konex-import' => $ki]);
    }
}
