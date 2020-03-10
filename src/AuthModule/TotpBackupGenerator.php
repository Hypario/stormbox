<?php

namespace App\AuthModule;

class TotpBackupGenerator
{
    /**
     * @var TotpBackupTable
     */
    private TotpBackupTable $backupTable;

    public function __construct(TotpBackupTable $backupTable)
    {
        $this->backupTable = $backupTable;
    }

    /**
     * Generate backup code for Totp
     * @param int $userId
     * @param int $length
     * @return array
     */
    public function generateBackupCodes(int $userId, int $length = 16): array
    {
        // check if user already have backup codes
        $backupCodes = $this->backupTable->findAllBy('user_id', $userId);
        if (!empty($codes = $backupCodes->getAll())) {
            // clean the user backup codes
            foreach ($codes as $code) {
                $this->backupTable->delete($code->id);
            }
        }

        $backupCodes = [];
        $alphanumerical = "abcdefghijklmnopqrstuvwxyz0123456789";

        for ($i = 0; $i < 10; $i++) {
            $code = "";
            for ($j = 0; $j < $length; $j++) {
                do {
                    try {
                        $retry = false;
                        $code .= $alphanumerical[random_int(0, strlen($alphanumerical) - 1)];
                    } catch (\Exception $e) {
                        $retry = true;
                    }
                } while ($retry);
            }
            $backupCodes[] = $code;
            $this->backupTable->insert([
                "user_id" => $userId,
                "hash" => hash("sha256", $code)
            ]);
        }

        return $backupCodes;
    }
}
