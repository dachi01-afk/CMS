da<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    use phpDocumentor\Reflection\Types\Nullable;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::create('pasien', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('user', 'id', 'pasien_user_id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
                $table->string('nama_pasien');
                $table->string('alamat')->nullable();
                $table->date('tanggal_lahir')->nullable();
                $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable();
                $table->string('foto_pasien')->nullable();
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('pasien');
        }
    };
