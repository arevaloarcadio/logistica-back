<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeShipment;

class TypeShipmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

      $type_shipment = new TypeShipment;
   		$type_shipment->name = 'Import';
      $type_shipment->icon_path = '/documents/xChsdutWBVmT6Tth9T0wH0Tp6C9BKcO6c1HK4Hb.svg';
   		$type_shipment->save();

      $type_shipment = new TypeShipment;
   		$type_shipment->name = 'Export';
      $type_shipment->icon_path = '/documents/Ghd2sGuBhiPjIjJ0MK0wu3z1VirkJKxiJmwe77dd.svg';
   		$type_shipment->save();

      $type_shipment = new TypeShipment;
   		$type_shipment->name = 'Nacional';
      $type_shipment->icon_path = '/documents/axChsdutWBVmT6Tth9T0wH0Tp6C9BKcO6c1HK4Hb.svg';
   		$type_shipment->save();
    }
}
