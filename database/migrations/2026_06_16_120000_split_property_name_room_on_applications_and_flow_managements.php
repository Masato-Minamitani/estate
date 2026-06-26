<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->text('property_name')->nullable()->after('staff_in_charge')->comment('物件名');
            $table->text('room_number')->nullable()->after('property_name')->comment('部屋番号');
        });

        Schema::table('flow_managements', function (Blueprint $table) {
            $table->text('property_name')->nullable()->after('staff_in_charge')->comment('物件名');
            $table->text('room_number')->nullable()->after('property_name')->comment('部屋番号');
        });

        foreach (DB::table('applications')->select('id', 'property_name_room')->cursor() as $row) {
            $split = $this->splitPropertyNameRoom($row->property_name_room);

            DB::table('applications')->where('id', $row->id)->update($split);
        }

        foreach (DB::table('flow_managements')->select('id', 'property_name_room')->cursor() as $row) {
            $split = $this->splitPropertyNameRoom($row->property_name_room);

            DB::table('flow_managements')->where('id', $row->id)->update($split);
        }

        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('property_name_room');
        });

        Schema::table('flow_managements', function (Blueprint $table) {
            $table->dropColumn('property_name_room');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->text('property_name_room')->nullable()->after('staff_in_charge')->comment('物件名＋部屋番号');
        });

        Schema::table('flow_managements', function (Blueprint $table) {
            $table->text('property_name_room')->nullable()->after('staff_in_charge')->comment('物件名＋部屋番号');
        });

        foreach (DB::table('applications')->select('id', 'property_name', 'room_number')->cursor() as $row) {
            DB::table('applications')->where('id', $row->id)->update([
                'property_name_room' => $this->combinePropertyNameRoom($row->property_name, $row->room_number),
            ]);
        }

        foreach (DB::table('flow_managements')->select('id', 'property_name', 'room_number')->cursor() as $row) {
            DB::table('flow_managements')->where('id', $row->id)->update([
                'property_name_room' => $this->combinePropertyNameRoom($row->property_name, $row->room_number),
            ]);
        }

        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['property_name', 'room_number']);
        });

        Schema::table('flow_managements', function (Blueprint $table) {
            $table->dropColumn(['property_name', 'room_number']);
        });
    }

    /**
     * @return array{property_name: ?string, room_number: ?string}
     */
    private function splitPropertyNameRoom(?string $value): array
    {
        $propertyNameRoom = trim((string) $value);

        if ($propertyNameRoom === '') {
            return [
                'property_name' => null,
                'room_number' => null,
            ];
        }

        $lastSpace = strrpos($propertyNameRoom, ' ');

        if ($lastSpace === false) {
            return [
                'property_name' => $propertyNameRoom,
                'room_number' => null,
            ];
        }

        return [
            'property_name' => substr($propertyNameRoom, 0, $lastSpace),
            'room_number' => substr($propertyNameRoom, $lastSpace + 1),
        ];
    }

    private function combinePropertyNameRoom(?string $propertyName, ?string $roomNumber): ?string
    {
        $combined = trim(trim((string) $propertyName).' '.trim((string) $roomNumber));

        return $combined === '' ? null : $combined;
    }
};
