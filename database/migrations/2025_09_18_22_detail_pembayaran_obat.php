    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::create('detail_pembayaran_obat', function (Blueprint $table) {
                $table->id('id_detail');
                $table->foreignId('pembayaran_id')->constrained('pembayaran', 'id_pembayaran')->cascadeOnDelete();
                $table->foreignId('resep_id')->constrained('resep_obat', 'id_resep')->cascadeOnDelete();
                $table->decimal('harga_satuan', 10, 2);
                $table->decimal('total_harga_item', 10, 2);
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('detail_pembayaran_obat');
        }
    };
