<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TriggerInsertOnStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
        CREATE TRIGGER trigger_insert_on_status AFTER INSERT ON `re_actions`
         FOR EACH ROW
            BEGIN
                SET @COUNT=(SELECT COUNT(nid) FROM totals WHERE nid=NEW.nid );
                IF @COUNT=0 THEN
                    INSERT INTO totals ( `nid`, `like`,`dislike`, `total`, `wilson`, `created_at`)
                    VALUES( NEW.nid , IF(NEW.status=1, 1, 0), IF(NEW.status=2, 1, 0), IF (NEW.status=0,0,1), IF(NEW.status=1, 0.2065, 0.0000), NOW());
                ELSE
                    UPDATE totals SET 
                        totals.like = IF(NEW.status=1, totals.like + 1, totals.like),
                        totals.dislike = IF(NEW.status=2, totals.dislike + 1, totals.dislike),
                        totals.total = IF(NEW.status!=0, totals.total + 1, totals.total)
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
