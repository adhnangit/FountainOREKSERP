<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== KEEPING ONLY HEAD OFFICE BRANCH ===\n\n";

$keepBranchId = 1; // HEAD OFFICE (COL)
$removeBranchIds = DB::table('branches')->where('id', '!=', $keepBranchId)->pluck('id')->all();

echo 'Keeping branch id: '.$keepBranchId." (HEAD OFFICE)\n";
echo 'Removing branch ids: '.implode(',', $removeBranchIds)."\n\n";

DB::statement('SET FOREIGN_KEY_CHECKS=0');

$n = DB::table('accounts')->whereIn('branch_id', $removeBranchIds)->count();
DB::table('accounts')->whereIn('branch_id', $removeBranchIds)->delete();
echo str_pad('accounts', 24).": deleted $n rows\n";

$n = DB::table('system_settings')->whereIn('branch_id', $removeBranchIds)->count();
DB::table('system_settings')->whereIn('branch_id', $removeBranchIds)->delete();
echo str_pad('system_settings', 24).": deleted $n rows\n";

$n = DB::table('document_sequences')->whereIn('branch_id', $removeBranchIds)->count();
DB::table('document_sequences')->whereIn('branch_id', $removeBranchIds)->delete();
echo str_pad('document_sequences', 24).": deleted $n rows\n";

$n = DB::table('branch_user')->whereIn('branch_id', $removeBranchIds)->count();
DB::table('branch_user')->whereIn('branch_id', $removeBranchIds)->delete();
echo str_pad('branch_user', 24).": deleted $n rows\n";

$n = DB::table('users')->whereIn('default_branch_id', $removeBranchIds)->count();
DB::table('users')->whereIn('default_branch_id', $removeBranchIds)->update(['default_branch_id' => null]);
echo str_pad('users.default_branch_id', 24).": cleared on $n rows\n";

$n = DB::table('branches')->whereIn('id', $removeBranchIds)->count();
DB::table('branches')->whereIn('id', $removeBranchIds)->delete();
echo str_pad('branches', 24).": deleted $n rows\n";

DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "\n=== DONE ===\n\n";

echo "Remaining branches:\n";
foreach (DB::table('branches')->get(['id', 'code', 'name']) as $b) {
    echo "  #{$b->id} {$b->code} {$b->name}\n";
}

echo "\nRemaining accounts:\n";
foreach (DB::table('accounts')->orderBy('group_id')->orderBy('code')->get(['code', 'name', 'branch_id']) as $a) {
    echo "  {$a->code}\t{$a->name}\t(branch_id={$a->branch_id})\n";
}
