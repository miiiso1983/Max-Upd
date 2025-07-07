<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AccountingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create chart of accounts
        $this->createChartOfAccounts();

        // Create sample transactions
        $this->createSampleTransactions();
    }

    /**
     * Create chart of accounts for pharmaceutical business
     */
    private function createChartOfAccounts()
    {
        $accounts = [
            // ASSETS
            [
                'code' => '1100',
                'name' => 'Cash',
                'name_ar' => 'النقد',
                'type' => 'asset',
                'subtype' => 'current_asset',
                'opening_balance' => 5000000,
                'is_system_account' => true,
                'created_by' => 1,
            ],
            [
                'code' => '1110',
                'name' => 'Bank - Rafidain Bank',
                'name_ar' => 'البنك - مصرف الرافدين',
                'type' => 'asset',
                'subtype' => 'current_asset',
                'opening_balance' => 15000000,
                'is_system_account' => true,
                'created_by' => 1,
            ],
            [
                'code' => '1200',
                'name' => 'Accounts Receivable',
                'name_ar' => 'الذمم المدينة',
                'type' => 'asset',
                'subtype' => 'current_asset',
                'opening_balance' => 3000000,
                'is_system_account' => true,
                'created_by' => 1,
            ],
            [
                'code' => '1300',
                'name' => 'Tax Receivable',
                'name_ar' => 'الضرائب المستحقة',
                'type' => 'asset',
                'subtype' => 'current_asset',
                'opening_balance' => 500000,
                'tax_account' => true,
                'created_by' => 1,
            ],

            // LIABILITIES
            [
                'code' => '2100',
                'name' => 'Accounts Payable',
                'name_ar' => 'الذمم الدائنة',
                'type' => 'liability',
                'subtype' => 'current_liability',
                'opening_balance' => 8000000,
                'is_system_account' => true,
                'created_by' => 1,
            ],
            [
                'code' => '2300',
                'name' => 'Tax Payable',
                'name_ar' => 'الضرائب المستحقة الدفع',
                'type' => 'liability',
                'subtype' => 'current_liability',
                'opening_balance' => 750000,
                'tax_account' => true,
                'created_by' => 1,
            ],

            // EQUITY
            [
                'code' => '3100',
                'name' => 'Capital',
                'name_ar' => 'رأس المال',
                'type' => 'equity',
                'subtype' => 'owners_equity',
                'opening_balance' => 100000000,
                'is_system_account' => true,
                'created_by' => 1,
            ],

            // REVENUE
            [
                'code' => '4100',
                'name' => 'Sales Revenue',
                'name_ar' => 'إيرادات المبيعات',
                'type' => 'revenue',
                'subtype' => 'operating_revenue',
                'is_system_account' => true,
                'created_by' => 1,
            ],

            // EXPENSES
            [
                'code' => '5100',
                'name' => 'Cost of Goods Sold',
                'name_ar' => 'تكلفة البضاعة المباعة',
                'type' => 'expense',
                'subtype' => 'operating_expense',
                'is_system_account' => true,
                'created_by' => 1,
            ],
            [
                'code' => '5200',
                'name' => 'Salaries & Wages',
                'name_ar' => 'الرواتب والأجور',
                'type' => 'expense',
                'subtype' => 'operating_expense',
                'created_by' => 1,
            ],
        ];

        foreach ($accounts as $accountData) {
            \App\Modules\Accounting\Models\Account::create($accountData);
        }
    }

    /**
     * Create sample transactions
     */
    private function createSampleTransactions()
    {
        // Sample journal entry
        $transaction = \App\Modules\Accounting\Models\Transaction::create([
            'type' => 'journal',
            'transaction_date' => now()->subDays(5),
            'description' => 'Opening balance entry',
            'description_ar' => 'قيد الرصيد الافتتاحي',
            'created_by' => 1,
        ]);

        // Create journal entries
        \App\Modules\Accounting\Models\JournalEntry::create([
            'transaction_id' => $transaction->id,
            'account_id' => 1, // Cash
            'type' => 'debit',
            'amount' => 1000000,
            'description' => 'Opening cash balance',
            'created_by' => 1,
        ]);

        \App\Modules\Accounting\Models\JournalEntry::create([
            'transaction_id' => $transaction->id,
            'account_id' => 7, // Capital
            'type' => 'credit',
            'amount' => 1000000,
            'description' => 'Initial capital investment',
            'created_by' => 1,
        ]);

        // Post the transaction
        $transaction->post(1);
    }
}
