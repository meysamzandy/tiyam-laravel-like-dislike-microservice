<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TriggerWilsonOnTotals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
        CREATE TRIGGER trigger_wilson_on_total BEFORE UPDATE ON `totals`
         FOR EACH ROW
            BEGIN
                SET @WILSON = 0;
                SET @LIKE = NEW.like;
                SET @DISLIKE = NEW.dislike;
                IF (@LIKE + @DISLIKE) > 0 THEN
                      SET @WILSON = (
                            (@LIKE + 1.9208) / (@LIKE + @DISLIKE) - 1.96 * 
                            SQRT ((@LIKE * @DISLIKE) / (@LIKE + @DISLIKE) + 0.9604) / (@LIKE + @DISLIKE)  
                      ) / (1 + 3.8416 / ((@LIKE + @DISLIKE)));
                              SET 
                                  NEW.wilson  = @WILSON,
                                  NEW.updated_at = NOW();
                 ELSE 
                            SET 
                               NEW.wilson  = 0.0000,
                               NEW.updated_at = NOW();
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
//        DB::unprepared('
//        CREATE TRIGGER trigger_wilson_on_total AFTER UPDATE ON `totals`
//         FOR EACH ROW
//            BEGIN
//                SET @WILSON = 0;
//                SET @LIKE = NEW.like;
//                SET @DISLIKE = NEW.dislike;
//                IF (NEW.like + NEW.dislike) > 0 THEN
//                      SET @WILSON = (
//                            (NEW.like + 1.9208) / (NEW.like + @DISLIKE) - 1.96 *
//                            SQRT ((@LIKE * @DISLIKE) / (@LIKE + @DISLIKE) + 0.9604) / (@LIKE + @DISLIKE)
//                      ) / (1 + 3.8416 / ((@LIKE + @DISLIKE)));
//
//                      SET @COUNT=(SELECT COUNT(nid) FROM wilsons WHERE nid=NEW.nid );
//                            UPDATE wilsons
//                                SET
//                                  wilsons.wilson  = @WILSON,
//                                  wilsons.updated_at = NOW()
//                            WHERE (nid = NEW.nid);
//                 END IF;
//            END
//
//
//        ');
    }
}
