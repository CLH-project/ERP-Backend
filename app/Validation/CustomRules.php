<?php 
namespace App\Validation;
class CustomRules
{
    public static function valid_cpf(string $str, string $fields = null, array $data = []): bool
{
    $cpf = preg_replace('/[^0-9]/', '', $str);

    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    for ($t = 9; $t < 11; $t++) {
        $d = 0;
        for ($c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$t] != $d) {
        return false;
        }
    }

    return true;
}
}