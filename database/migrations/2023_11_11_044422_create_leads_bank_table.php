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
        Schema::create('leads_bank', function (Blueprint $table) {
            $table->id();
//            $table->unsignedBigInteger('user_id');
//            $table->enum('customer_type',['residential','commercial']);
//            $table->string('first_name');
//            $table->string('last_name');
//            $table->string('phone')->default("")->nullable();
//            $table->boolean('is_phone_receives_txt');
//            $table->string('mobile');
//            $table->boolean('is_mobile_receives_txt');
//            $table->string('email')->default("")->nullable();
//            $table->string('preferred_language');
//            $table->string('location')->default("")->nullable();
//            $table->decimal('lat', 17,15)->default(0)->nullable();
//            $table->decimal('lng', 17,14)->default(0)->nullable();
//            $table->string('street');
//            $table->string('unit')->default("")->nullable();
//            $table->foreignId('country_id');
//            $table->foreignId('state_id');
//            $table->foreignId('city_id');
//            $table->string('county')->default("")->nullable();
//            $table->enum('citizenship_status', ['US_CITIZEN','LAWFUL_PERMANENT_RESIDENT_ALIEN','OTHER'])->nullable();
//            $table->enum('home_occupancy', ['PRIMARY','SECONDARY','INVESTMENT','OTHER'])->nullable();
//            $table->boolean('is_active')->default(1);
//            $table->boolean('is_hoa')->default(1);
//            $table->string('days')->default(0);
//            $table->enum('status',['hot','warm','neutral','cold','frozen','back_to_leads_bank','client'])->default('hot');
//            $table->enum('sort_status',[1,2,3,4,5,6,7])->default(1);
//            $table->enum('source',['manual','call_center','apn'])->default('manual');
//            $table->timestamp('modified_at')->nullable();
//            $table->string('company_name')->default("")->nullable();
//            $table->string('company_business_model')->default("")->nullable();
//            $table->enum('building_type',['single_family','residence','trailer','town_home']);
//            $table->string('time_to_contact', 255);
//            $table->enum('way_to_contact',['phone','email']);
//            $table->enum('lead_type',['lead_guaranteed','self_generated'])->default('lead_guaranteed');
//            $table->boolean('is_decision_maker_present')->default(1);
//            $table->enum('house_ownership',['rental','owner','house_ownership'])->default('owner');
//            $table->string('zip_code');
//
//            $table->integer("rate");
//            $table->timestamp("last_time_communicated")->nullable();
//            $table->enum("transaction_type",['commission based','immediate']);
//            $table->double('price_percentage');
//            $table->enum("commission_based",['flat rate','split earning','percentage of sale'])->nullable();
//            $table->enum("commission_type",['shared','exclusive'])->nullable();
//            $table->text("description")->default("")->nullable();
//            $table->boolean("is_archive")->default(1);



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
        Schema::dropIfExists('leads_bank');
    }
};
