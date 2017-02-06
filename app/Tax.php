<?php

namespace App;

class Tax
{
    protected $taxes = [
       'alberta'                   => 0.05,
       'britishcolumbia'           => 0.05,
       'manitoba'                  => 0.05,
       'newbrunswick'              => 0.05,
       'northwestterritories'      => 0.05,
       'novascotia'                => 0.05,
       'nunavut'                   => 0.05,
       'ontario'                   => 0.13,
       'quebec'                    => 0.05,
       'saskatchewan'              => 0.05,
       'yukon'                     => 0.05,
       
       'princeedwardisland'        => 0.05,
       'pei'                       => 0.05,
       
       'newfoundlandandlabrador'   => 0.05,
       'newfoundland'              => 0.05,
       'labrador'                  => 0.05,
   ];

    public function getTaxByProvince ($province)
    {
        $province = $this->stripAccents(strtolower(preg_replace('/\s+/', '', $province)));

        if (!isset($this->taxes[$province])) { return 0; }

        $rate = $this->taxes[$province];

        if (!$rate) return (float) 0;

        return (float) $rate;
    }

    public static function get ($province)
    {
        $tax = new Tax;

        return $tax->getTaxByProvince ($province);
    }

    public function stripAccents ($str)
    {
        return strtr(
            utf8_decode($str),
            utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'),
            'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY'
        );
    }
}
