<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TriggerUpdateOnStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
        CREATE TRIGGER trigger_update_on_status AFTER UPDATE ON `re_actions`
         FOR EACH ROW
            BEGIN
                        IF NEW.status = 0 AND OLD.status = 1 THEN
                             UPDATE totals 
                                SET 
                                    totals.like  = totals.like - 1,
                                    totals.total = totals.total - 1,
                                    totals.updated_at = NOW()
                             WHERE (nid = NEW.nid);   
                        ELSEIF NEW.status = 0  AND OLD.status = 2 THEN
                            UPDATE totals 
                                SET 
                                    totals.dislike  = totals.dislike - 1,
                                    totals.total = totals.total - 1,
                                    totals.updated_at = NOW()
                            WHERE (nid = NEW.nid);    
                        ELSEIF NEW.status = 1  AND OLD.status = 0 THEN
                            UPDATE totals 
                                SET 
                                    totals.like  = totals.like + 1,
                                    totals.total = totals.total + 1,
                                    totals.updated_at = NOW()
                            WHERE (nid = NEW.nid);   
                        ELSEIF NEW.status = 2  AND OLD.status = 0 THEN
                            UPDATE totals 
                                SET 
                                    totals.dislike  = totals.dislike + 1,
                                    totals.total = totals.total + 1,
                                    totals.updated_at = NOW()
                            WHERE (nid = NEW.nid);    
                        ELSEIF NEW.status = 1  AND OLD.status = 2 THEN
                            UPDATE totals 
                                SET 
                                    totals.like  = totals.like + 1,
                                    totals.dislike  = totals.dislike - 1,
                                    totals.updated_at = NOW()
                            WHERE (nid = NEW.nid);    
                        ELSEIF NEW.status = 2  AND OLD.status = 1 THEN
                            UPDATE totals 
                                SET 
                                    totals.like  = totals.like - 1,
                                    totals.dislike  = totals.dislike + 1,
                                    totals.updated_at = NOW()
                            WHERE (nid = NEW.nid); 
                        END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
