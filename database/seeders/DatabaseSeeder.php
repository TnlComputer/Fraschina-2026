<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    $this->call(RolesAndPermissionsSeeder::class);

    // UsersTableSeeder tiene los usuarios reales: no se versiona (ver .gitignore).
    // Copiar UsersTableSeeder.php.example como UsersTableSeeder.php y completarlo.
    if (class_exists(UsersTableSeeder::class)) {
      $this->call(UsersTableSeeder::class);
    } else {
      $this->command?->warn('Sin UsersTableSeeder.php: no se crearon usuarios (ver UsersTableSeeder.php.example).');
    }
  }
}
