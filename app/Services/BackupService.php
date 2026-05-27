<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BackupService
{
    /**
     * Generate backup for specific tables or all tables
     */
    public static function backup(array $tables, string $filename)
    {
        try {
            $sql = "-- SQL Dump\n";
            $sql .= "-- Generated: " . now()->toDateTimeString() . "\n";
            $sql .= "-- Database: " . config('database.connections.mysql.database') . "\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                // Drop Table statement
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                
                // Get Create Table statement
                $createResult = DB::select("SHOW CREATE TABLE `{$table}`");
                if (empty($createResult)) {
                    continue;
                }
                $createResultArray = (array) $createResult[0];
                // The column name representing the create statement might be 'Create Table'
                $createStatement = $createResultArray['Create Table'] ?? array_values($createResultArray)[1];
                $sql .= $createStatement . ";\n\n";
                
                // Insert Data statements
                $rows = DB::table($table)->get();
                if ($rows->count() > 0) {
                    $sql .= "LOCK TABLES `{$table}` WRITE;\n";
                    $sql .= "INSERT INTO `{$table}` VALUES ";
                    
                    $valuesList = [];
                    foreach ($rows as $row) {
                        $rowArray = (array) $row;
                        $escapedValues = array_map(function($value) {
                            if (is_null($value)) {
                                return 'NULL';
                            }
                            // Escape special characters for SQL insert
                            return "'" . addslashes($value) . "'";
                        }, array_values($rowArray));
                        
                        $valuesList[] = "\n(" . implode(', ', $escapedValues) . ")";
                    }
                    
                    $sql .= implode(', ', $valuesList) . ";\n";
                    $sql .= "UNLOCK TABLES;\n\n";
                }
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            // Store in storage/app/backups
            if (!Storage::exists('backups')) {
                Storage::makeDirectory('backups');
            }

            Storage::put('backups/' . $filename, $sql);
            Log::info("Backup {$filename} berhasil dibuat secara native.");
            return true;
        } catch (\Exception $e) {
            Log::error("Gagal membuat backup {$filename}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Perform full database backup
     */
    public static function backupFull(string $filename)
    {
        $databaseName = config('database.connections.mysql.database');
        $tablesQuery = DB::select("SHOW TABLES");
        $tables = [];
        
        foreach ($tablesQuery as $tableRow) {
            $tableRowArray = (array) $tableRow;
            $tables[] = array_values($tableRowArray)[0];
        }

        return self::backup($tables, $filename);
    }

    /**
     * Perform guest table backup
     */
    public static function backupGuests(string $filename)
    {
        return self::backup(['guests'], $filename);
    }

    /**
     * Restore only guest table from backup file
     */
    public static function restoreGuests(string $filename)
    {
        try {
            if (!Storage::exists('backups/' . $filename)) {
                throw new \Exception("File backup tidak ditemukan.");
            }

            $sql = Storage::get('backups/' . $filename);
            
            // Disable foreign keys temporarily
            DB::statement("SET FOREIGN_KEY_CHECKS=0;");
            // Run database statements
            DB::unprepared($sql);
            // Re-enable foreign keys
            DB::statement("SET FOREIGN_KEY_CHECKS=1;");

            Log::info("Restore data buku tamu dari {$filename} berhasil dilakukan.");
            return true;
        } catch (\Exception $e) {
            Log::error("Gagal restore buku tamu dari {$filename}: " . $e->getMessage());
            throw $e;
        }
    }
}
