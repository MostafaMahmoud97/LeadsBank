<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("leads_bank_id");
            $table->unsignedBigInteger("leads_id");
            $table->unsignedBigInteger("client_id");
            $table->string("name");
            $table->string("email");
            $table->string("phone")->default("")->nullable();
            $table->enum("status",["sold","proposal created","contract created","contract signed","immediate"]);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
};
