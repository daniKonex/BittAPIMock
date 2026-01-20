<?php

namespace App\Data;

class CommercialClientsMapping
{
    private static $mappingFile = WRITEPATH . 'cache/commercial_clients_mapping.json';
    
    /**
     * Get or create consistent client data for a commercial email
     */
    public static function getClientsForEmail(string $email): array
    {
        $mapping = self::loadMapping();
        
        // If email already has clients, return them
        if (isset($mapping[$email])) {
            return $mapping[$email];
        }
        
        // Generate 3 new clients for this email
        $clients = self::generateClientsForEmail($email);
        
        // Save mapping
        $mapping[$email] = $clients;
        self::saveMapping($mapping);
        
        return $clients;
    }
    
    /**
     * Generate 3 consistent clients for a commercial email
     */
    private static function generateClientsForEmail(string $email): array
    {
        $baseHash = substr(md5($email), 0, 8);
        
        return [
            [
                'clientId' => 'CLI-' . strtoupper(substr($baseHash, 0, 4)) . '01',
                'email' => str_replace('comercial', 'cliente1', $email),
                'name' => 'Cliente Principal S.L.',
                'canCreateOrders' => true,
                'addresses' => [
                    [
                        'id' => 'ADDR-' . strtoupper(substr($baseHash, 0, 4)) . '01-01',
                        'type' => 'shipping',
                        'name' => 'Cliente Principal S.L.',
                        'street' => 'Calle Principal 123',
                        'city' => 'Madrid',
                        'postalCode' => '28001',
                        'province' => 'Madrid',
                        'country' => 'ES',
                        'phone' => '+34 910 000 001',
                        'isDefault' => true
                    ],
                    [
                        'id' => 'ADDR-' . strtoupper(substr($baseHash, 0, 4)) . '01-02',
                        'type' => 'shipping',
                        'name' => 'Cliente Principal S.L. - Almacén',
                        'street' => 'Polígono Industrial 45',
                        'city' => 'Alcalá de Henares',
                        'postalCode' => '28802',
                        'province' => 'Madrid',
                        'country' => 'ES',
                        'phone' => '+34 910 000 002',
                        'isDefault' => false
                    ]
                ]
            ],
            [
                'clientId' => 'CLI-' . strtoupper(substr($baseHash, 0, 4)) . '02',
                'email' => str_replace('comercial', 'cliente2', $email),
                'name' => 'Cliente Secundario S.A.',
                'canCreateOrders' => true,
                'addresses' => [
                    [
                        'id' => 'ADDR-' . strtoupper(substr($baseHash, 0, 4)) . '02-01',
                        'type' => 'shipping',
                        'name' => 'Cliente Secundario S.A.',
                        'street' => 'Avenida Secundaria 456',
                        'city' => 'Barcelona',
                        'postalCode' => '08001',
                        'province' => 'Barcelona',
                        'country' => 'ES',
                        'phone' => '+34 930 000 001',
                        'isDefault' => true
                    ],
                    [
                        'id' => 'ADDR-' . strtoupper(substr($baseHash, 0, 4)) . '02-02',
                        'type' => 'shipping',
                        'name' => 'Cliente Secundario S.A. - Sucursal',
                        'street' => 'Calle Diagonal 789',
                        'city' => 'Barcelona',
                        'postalCode' => '08019',
                        'province' => 'Barcelona',
                        'country' => 'ES',
                        'phone' => '+34 930 000 002',
                        'isDefault' => false
                    ]
                ]
            ],
            [
                'clientId' => 'CLI-' . strtoupper(substr($baseHash, 0, 4)) . '03',
                'email' => str_replace('comercial', 'cliente3', $email),
                'name' => 'Cliente Terciario S.L.',
                'canCreateOrders' => true,
                'addresses' => [
                    [
                        'id' => 'ADDR-' . strtoupper(substr($baseHash, 0, 4)) . '03-01',
                        'type' => 'shipping',
                        'name' => 'Cliente Terciario S.L.',
                        'street' => 'Plaza Terciaria 789',
                        'city' => 'Valencia',
                        'postalCode' => '46001',
                        'province' => 'Valencia',
                        'country' => 'ES',
                        'phone' => '+34 960 000 001',
                        'isDefault' => true
                    ],
                    [
                        'id' => 'ADDR-' . strtoupper(substr($baseHash, 0, 4)) . '03-02',
                        'type' => 'shipping',
                        'name' => 'Cliente Terciario S.L. - Delegación',
                        'street' => 'Calle Puerto 321',
                        'city' => 'Valencia',
                        'postalCode' => '46024',
                        'province' => 'Valencia',
                        'country' => 'ES',
                        'phone' => '+34 960 000 002',
                        'isDefault' => false
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Load mapping from file
     */
    private static function loadMapping(): array
    {
        if (!file_exists(self::$mappingFile)) {
            return [];
        }
        
        $content = file_get_contents(self::$mappingFile);
        return json_decode($content, true) ?? [];
    }
    
    /**
     * Save mapping to file
     */
    private static function saveMapping(array $mapping): void
    {
        $dir = dirname(self::$mappingFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents(self::$mappingFile, json_encode($mapping, JSON_PRETTY_PRINT));
    }
}
