<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class StockFullTest extends ApiTestCase
{
    /**
    * @expectedException NotEnoughProductException
    */
    public function testCreateStorageBrandProductAndProductProperty(): void
    {
        $client = static::createClient($options = ['headers' => ['Content-Type' => 'application/ld+json']]);
        /**
         * Raktár 1 létrehozása
         */
        $client->request('POST', '/api/storages', [
            'json' => [
                'name' => 'Raktár 1',
                'address' => 'Budapest, Váci út 143',
                'capacity' => 300,
            ]
        ]);
        
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['name' => 'Raktár 1']);
        $data = $client->getResponse()->toArray();
        $this->assertEquals($data['capacity'], $data['availableSpace']);

        /**
         * Raktár 1 azonosítóját mentem későbbi hívásokhoz
         */
        $storage1Id = $data['@id'];

        /**
         * Raktár 2 létrehozása
         */
        $client->request('POST', '/api/storages', [
            'json' => [
                'name' => 'Raktár 2',
                'address' => 'Debrecen, Kishegyesi 30',
                'capacity' => 100,
            ]
        ]);
        $storage2Id = $client->getResponse()->toArray()['@id'];

        /**
         * Brand létrehozása
         */
        $client->request('POST', '/api/brands', [
            'json' => [
                'name' => 'Apple',
                'qualityCategory' => 5,
            ]
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['name' => 'Apple']);        
        $brandId = $client->getResponse()->toArray()['@id'];

        /**
         * Termék 1 létrehozása
         */
        $client->request('POST', '/api/products', [
            'json' => [
                'name' => 'iPhone 13 Max',
                'class' => 'Telefon',
                'price' => 560000,
                'unitSize' => 1,
                'brand' => $brandId
            ]
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['name' => 'iPhone 13 Max']);      
        $data = $client->getResponse()->toArray();
        $this->assertEquals($data['brand']['@id'], $brandId);
        $product1Id = $client->getResponse()->toArray()['@id'];        

        /**
         * Termék 2 létrehozása
         */
        $client->request('POST', '/api/products', [
            'json' => [
                'name' => 'iPad Pro 11',
                'class' => 'Tablet',
                'price' => 360000,
                'unitSize' => 1,
                'brand' => $brandId
            ]
        ]);
        $this->assertResponseIsSuccessful();
        $product2Id = $client->getResponse()->toArray()['@id']; 

        /**
         * Termék 1-hez extra tulajdonság hozzáadása
         */
        $client->request('POST', '/api/product_properties', [
            'json' => [
                'propertyName' => 'Capacity',
                'value' => '128GB',
                'product' => $product1Id
            ]
        ]);
        $this->assertResponseIsSuccessful();
        $data = $client->getResponse()->toArray();
        $this->assertEquals($data['product'], $product1Id);        


        /**
         * Termék 1 100 db bevételezése Raktár 1be, marad 200 hely szabadon
         */
        $client->request('POST', '/api/stock_changes', [
            'json' => [
                'quantity' => 100,
                'storage' => $storage1Id,
                'product' => $product1Id
            ]
        ]);
        $this->assertResponseIsSuccessful();

        /**
         * Termék 1 100 db bevételezése Raktár 2be, nem marad szabad hely
         */
        $client->request('POST', '/api/stock_changes', [
            'json' => [
                'quantity' => 100,
                'storage' => $storage2Id,
                'product' => $product1Id
            ]
        ]);
        $this->assertResponseIsSuccessful();        

        /**
         * Raktár 2 szabad hely ellenőrzése és Termék 1 megléte a raktárban
         */
        $client->request('GET', $storage2Id);
        $this->assertResponseIsSuccessful();     
        $data = $client->getResponse()->toArray();
        $this->assertEquals($data['availableSpace'], 0);  
        $this->assertEquals($data['stocks'][0]['product']['@id'], $product1Id);


        /**
         * Termék 2 50 db bevételezése Raktár 2be, de mivel ott nincs hely
         * ezért átkerül Raktár 1be, ott lévén szabad hely
         */
        $client->request('POST', '/api/stock_changes', [
            'json' => [
                'quantity' => 50,
                'storage' => $storage2Id,
                'product' => $product2Id
            ]
        ]);
        $this->assertResponseIsSuccessful();       

        /**
         * Raktár 1 ellenőrzése, hogy csökkent-e 50el a szabad hely és bekerült-e Termék 1
         */        
        $client->request('GET', $storage1Id);
        $this->assertResponseIsSuccessful();     
        $data = $client->getResponse()->toArray();
        $this->assertEquals($data['availableSpace'], 150);  
        $this->assertEquals($data['stocks'][1]['product']['@id'], $product2Id);


        /**
         * Raktár 2ből 50 db Termék 1 kivétele
         */
        $client->request('POST', '/api/stock_changes', [
            'json' => [
                'quantity' => -50,
                'storage' => $storage2Id,
                'product' => $product1Id
            ]
        ]);
        $this->assertResponseIsSuccessful();  

        /**
         * Raktár 2 szabad hely ellenőrzés
         */
        $client->request('GET', $storage2Id);
        $this->assertResponseIsSuccessful();     
        $data = $client->getResponse()->toArray();
        $this->assertEquals($data['availableSpace'], 50);  

        /**
         * Raktár 2ből 100 db Termék 1 kivétele, mivel nincs ott elég,
         * ezért a Raktár 1ből is kivételre kerül 50
         */
        $client->request('POST', '/api/stock_changes', [
            'json' => [
                'quantity' => -100,
                'storage' => $storage2Id,
                'product' => $product1Id
            ]
        ]);
        $this->assertResponseIsSuccessful();  

        /**
         * Raktár 1 ellenőrzése
         */
        $client->request('GET', $storage1Id);
        $this->assertResponseIsSuccessful();     
        $data = $client->getResponse()->toArray();
        $this->assertEquals($data['availableSpace'], 200); 

    }
}
