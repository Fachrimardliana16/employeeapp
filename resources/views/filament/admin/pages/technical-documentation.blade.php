<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-3">
                    <x-filament::icon icon="heroicon-o-code-bracket" class="w-8 h-8 text-primary-600"/>
                    <span class="text-2xl font-bold">Technical Documentation</span>
                </div>
            </x-slot>
            <p class="text-gray-600 dark:text-gray-400">
                Comprehensive technical documentation for developers and system administrators.
                This covers system architecture, database schema, service classes, and development guidelines.
            </p>
        </x-filament::section>

        {{-- System Architecture --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">🏗️ System Architecture</x-slot>

            <div class="space-y-4">
                <h4 class="font-bold text-lg">Technology Stack</h4>
                <ul class="list-disc ml-6">
                    <li><strong>Framework:</strong> Laravel 12.21.0</li>
                    <li><strong>PHP Version:</strong> 8.4.7</li>
                    <li><strong>Admin Panel:</strong> Filament v3.3.35</li>
                    <li><strong>Database:</strong> MySQL with foreign key constraints</li>
                    <li><strong>Authentication:</strong> Laravel Sanctum</li>
                    <li><strong>Authorization:</strong> Spatie Laravel Permission 6.21.0</li>
                    <li><strong>PDF Generation:</strong> DomPDF</li>
                    <li><strong>File Storage:</strong> Laravel Storage (local/cloud)</li>
                </ul>

                <h4 class="font-bold text-lg mt-4">Panel Structure</h4>
                <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg font-mono text-sm">
                    <pre>
app/Filament/
├── Admin/              # Full system management
│   ├── Resources/      # Master data & payroll config
│   └── Pages/          # Custom pages (Technical Docs)
│
├── Employee/           # HR operations
│   ├── Resources/      # Employee data, payroll processing
│   └── Pages/          # Manual book, reports
│
└── User/               # Employee self-service
    ├── Resources/      # Personal data input (permissions, resign)
    └── Pages/          # Data view, attendance, manual book</pre>
                </div>

                <h4 class="font-bold text-lg mt-4">Key Design Patterns</h4>
                <ul class="list-disc ml-6">
                    <li><strong>Service Layer:</strong> PayrollService for complex business logic</li>
                    <li><strong>Observer Pattern:</strong> Auto-populate user_id, employee_id on create</li>
                    <li><strong>Resource Pattern:</strong> Filament resources for CRUD operations</li>
                    <li><strong>Approval Workflow:</strong> Pending → Approved/Rejected with notes</li>
                    <li><strong>Soft Deletes:</strong> Most models use SoftDeletes trait</li>
                </ul>
            </div>
        </x-filament::section>

        {{-- Database Schema --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">💾 Database Schema</x-slot>

            <div class="space-y-4">
                <h4 class="font-bold text-lg">Core Tables</h4>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold">Table</th>
                                <th class="px-4 py-2 text-left font-semibold">Purpose</th>
                                <th class="px-4 py-2 text-left font-semibold">Key Fields</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <td class="px-4 py-2 font-mono">employees</td>
                                <td class="px-4 py-2">Employee master data</td>
                                <td class="px-4 py-2">name, nik, email, department_id, position_id, grade_id, employment_status</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-mono">employee_salaries</td>
                                <td class="px-4 py-2">Salary history</td>
                                <td class="px-4 py-2">employee_id, base_salary, effective_date, is_current</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-mono">employee_permissions</td>
                                <td class="px-4 py-2">Leave/permission requests</td>
                                <td class="px-4 py-2">employee_id, permission_type, start_date, end_date, approval_status, approved_by</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-mono">employee_retirements</td>
                                <td class="px-4 py-2">Resignation/retirement</td>
                                <td class="px-4 py-2">employee_id, retirement_type, effective_date, last_working_day, handover_notes, approval_status</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-mono">employee_documents</td>
                                <td class="px-4 py-2">Document management</td>
                                <td class="px-4 py-2">employee_id, document_type, document_number, file_path, expiry_date, uploaded_by</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-mono">employee_attendance_records</td>
                                <td class="px-4 py-2">GPS-based attendance</td>
                                <td class="px-4 py-2">employee_id, check_in_time, check_out_time, latitude, longitude, total_hours</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-mono">employee_daily_reports</td>
                                <td class="px-4 py-2">Daily activity reports</td>
                                <td class="px-4 py-2">employee_id, report_date, activities, progress_percentage, issues</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h4 class="font-bold text-lg mt-6">Payroll Tables</h4>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold">Table</th>
                                <th class="px-4 py-2 text-left font-semibold">Purpose</th>
                                <th class="px-4 py-2 text-left font-semibold">Key Fields</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <td class="px-4 py-2 font-mono">payroll_formulas</td>
                                <td class="px-4 py-2">Calculation formulas by status/grade/position</td>
                                <td class="px-4 py-2">formula_code, applies_to_type, applies_to_value, formula_components (JSON), percentage_multiplier</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-mono">payroll_components</td>
                                <td class="px-4 py-2">Allowances, deductions, bonuses</td>
                                <td class="px-4 py-2">component_code, component_name, component_type, calculation_method, amount/percentage, is_taxable</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-mono">employee_payrolls</td>
                                <td class="px-4 py-2">Monthly payroll records</td>
                                <td class="px-4 py-2">employee_id, period_month, period_year, base_salary, total_allowances, total_deductions, net_salary, payment_status</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-mono">employee_payroll_details</td>
                                <td class="px-4 py-2">Payroll calculation breakdown</td>
                                <td class="px-4 py-2">employee_payroll_id, component_id, component_type, calculation_basis, amount</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-mono">employee_salary_cuts</td>
                                <td class="px-4 py-2">Installment deductions</td>
                                <td class="px-4 py-2">employee_id, cut_type, total_amount, installment_months, paid_months, monthly_deduction, is_active</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h4 class="font-bold text-lg mt-6">Master Data Tables</h4>
                <ul class="list-disc ml-6">
                    <li><code>master_departments</code> - Organizational departments</li>
                    <li><code>master_positions</code> - Job positions/titles</li>
                    <li><code>master_employee_grades</code> - Employee grades with benefits</li>
                    <li><code>master_employee_grade_benefits</code> - Benefits per grade</li>
                    <li><code>master_employee_basic_salaries</code> - Base salary ranges</li>
                    <li><code>master_standar_harga_satuans</code> - Standard cost units for business travel (SHS)</li>
                </ul>

                <h4 class="font-bold text-lg mt-6">Key Relationships</h4>
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg font-mono text-sm">
                    <pre>
Employee → hasMany(EmployeeSalary)
Employee → hasMany(EmployeePermission)
Employee → hasMany(EmployeeDocument)
Employee → hasMany(EmployeePayroll)
Employee → hasMany(EmployeeSalaryCut)
Employee → belongsTo(MasterDepartment)
Employee → belongsTo(MasterPosition)
Employee → belongsTo(MasterEmployeeGrade)

EmployeePayroll → hasMany(EmployeePayrollDetail)
EmployeePayroll → belongsTo(Employee)

PayrollFormula → hasMany(PayrollComponent) via formula_components JSON
                    </pre>
                </div>
            </div>
        </x-filament::section>

        {{-- PayrollService Documentation --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">💰 PayrollService Documentation</x-slot>

            <div class="space-y-4">
                <h4 class="font-bold text-lg">Class: <code>App\Services\PayrollService</code></h4>
                <p>Handles complex payroll calculations with support for different employment statuses.</p>

                <h5 class="font-semibold mt-4">Main Method: calculatePayroll()</h5>
                <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                    <pre class="text-sm font-mono">
public function calculatePayroll(
    Employee $employee,
    int $month,
    int $year
): EmployeePayroll</pre>
                </div>

                <h5 class="font-semibold mt-4">Calculation Logic</h5>
                <ol class="list-decimal ml-6">
                    <li><strong>Get Base Salary:</strong> From employee_salaries where is_current = true</li>
                    <li><strong>Get Attendance:</strong> Count working days from employee_attendance_records</li>
                    <li><strong>Load Formula:</strong> Find matching PayrollFormula by employment_status/grade/position</li>
                    <li><strong>Calculate Components:</strong>
                        <ul class="list-circle ml-6">
                            <li><strong>Allowances:</strong> Fixed amounts or % of base salary</li>
                            <li><strong>Deductions:</strong> Tax, insurance, salary cuts (installments)</li>
                            <li><strong>Bonuses:</strong> Performance-based or fixed</li>
                        </ul>
                    </li>
                    <li><strong>Apply Status-Specific Rules:</strong>
                        <ul class="list-circle ml-6">
                            <li><strong>THL (Daily Worker):</strong> Base salary × working days</li>
                            <li><strong>CAPEG:</strong> Apply 80% multiplier from formula</li>
                            <li><strong>Contract/PNS:</strong> Full base salary regardless of attendance</li>
                        </ul>
                    </li>
                    <li><strong>Calculate Net:</strong> Gross - Total Deductions</li>
                    <li><strong>Save to Database:</strong> Create EmployeePayroll + EmployeePayrollDetail records</li>
                </ol>

                <h5 class="font-semibold mt-4">Example Usage</h5>
                <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                    <pre class="text-sm font-mono">
use App\Services\PayrollService;

$payrollService = new PayrollService();
$employee = Employee::find(1);

// Generate payroll for January 2026
$payroll = $payrollService->calculatePayroll($employee, 1, 2026);

echo "Net Salary: " . $payroll->net_salary;</pre>
                </div>

                <h5 class="font-semibold mt-4">Helper Methods</h5>
                <ul class="list-disc ml-6">
                    <li><code>getBaseSalary(Employee $employee)</code> - Get current base salary</li>
                    <li><code>getWorkingDays(Employee $employee, $month, $year)</code> - Count attendance days</li>
                    <li><code>getApplicableFormula(Employee $employee)</code> - Find matching formula</li>
                    <li><code>calculateAllowances(Employee $employee, $baseSalary)</code> - Sum all allowances</li>
                    <li><code>calculateDeductions(Employee $employee, $month, $year)</code> - Sum all deductions</li>
                    <li><code>getActiveSalaryCuts(Employee $employee)</code> - Get active installments</li>
                </ul>
            </div>
        </x-filament::section>

        {{-- Approval Workflow --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">✅ Approval Workflow Pattern</x-slot>

            <div class="space-y-4">
                <h4 class="font-bold text-lg">Standard Workflow</h4>
                <p>Used in: EmployeePermission, EmployeeRetirement</p>

                <h5 class="font-semibold mt-4">Database Fields</h5>
                <ul class="list-disc ml-6">
                    <li><code>approval_status</code> - ENUM: pending, approved, rejected</li>
                    <li><code>approved_by</code> - Foreign key to users table</li>
                    <li><code>approved_at</code> - Timestamp of approval/rejection</li>
                    <li><code>approval_notes</code> - Comments from approver</li>
                </ul>

                <h5 class="font-semibold mt-4">Implementation Steps</h5>
                <ol class="list-decimal ml-6">
                    <li><strong>Create:</strong> Observer auto-sets approval_status = 'pending'</li>
                    <li><strong>Review:</strong> HR/Admin views pending requests</li>
                    <li><strong>Approve/Reject:</strong> Custom Action in Filament Resource
                        <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded mt-2">
                            <pre class="text-xs font-mono">
Actions\Action::make('approve')
    ->requiresConfirmation()
    ->form([
        Textarea::make('approval_notes')
            ->label('Catatan Persetujuan')
    ])
    ->action(function (Model $record, array $data) {
        $record->update([
            'approval_status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'approval_notes' => $data['approval_notes'] ?? null,
        ]);
    })</pre>
                        </div>
                    </li>
                    <li><strong>Notification:</strong> Send email/notification to employee</li>
                </ol>

                <h5 class="font-semibold mt-4">Table Display</h5>
                <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded">
                    <pre class="text-xs font-mono">
Tables\Columns\BadgeColumn::make('approval_status')
    ->label('Status')
    ->colors([
        'warning' => 'pending',
        'success' => 'approved',
        'danger' => 'rejected',
    ])</pre>
                </div>
            </div>
        </x-filament::section>

        {{-- File Upload System --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">📎 File Upload System</x-slot>

            <div class="space-y-4">
                <h4 class="font-bold text-lg">Configuration</h4>
                <ul class="list-disc ml-6">
                    <li><strong>Storage:</strong> <code>storage/app/public/employee-documents/</code></li>
                    <li><strong>Max File Size:</strong> 10MB</li>
                    <li><strong>Allowed Types:</strong> PDF, JPG, PNG, JPEG</li>
                    <li><strong>Disk:</strong> public (symlinked to public/storage)</li>
                </ul>

                <h5 class="font-semibold mt-4">Filament FileUpload Implementation</h5>
                <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded">
                    <pre class="text-xs font-mono">
FileUpload::make('file_path')
    ->label('Upload Dokumen')
    ->disk('public')
    ->directory('employee-documents')
    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
    ->maxSize(10240) // 10MB in KB
    ->downloadable()
    ->previewable()
    ->required()</pre>
                </div>

                <h5 class="font-semibold mt-4">Expiry Tracking</h5>
                <p>For documents with expiration dates (KTP, NPWP, BPJS):</p>
                <ul class="list-disc ml-6">
                    <li><strong>issue_date:</strong> Document issue date</li>
                    <li><strong>expiry_date:</strong> Document expiration date</li>
                    <li><strong>Filters:</strong>
                        <ul class="list-circle ml-6">
                            <li>"Kadaluarsa" - expiry_date < today()</li>
                            <li>"Akan Kadaluarsa" - expiry_date between today() and +30 days</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </x-filament::section>

        {{-- Development Guidelines --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">🛠️ Development Guidelines</x-slot>

            <div class="space-y-4">
                <h4 class="font-bold text-lg">Adding New Resource</h4>
                <ol class="list-decimal ml-6">
                    <li>Create Model & Migration:
                        <div class="bg-gray-50 dark:bg-gray-800 p-2 rounded mt-1">
                            <code class="text-xs">php artisan make:model EmployeeXxx -m</code>
                        </div>
                    </li>
                    <li>Add relationships in Model</li>
                    <li>Create Observer if needed (auto-populate fields)</li>
                    <li>Generate Filament Resource:
                        <div class="bg-gray-50 dark:bg-gray-800 p-2 rounded mt-1">
                            <code class="text-xs">php artisan make:filament-resource EmployeeXxx --panel=employee</code>
                        </div>
                    </li>
                    <li>Configure form fields in Resource</li>
                    <li>Configure table columns & filters</li>
                    <li>Add navigation group & icon</li>
                    <li>Test CRUD operations</li>
                </ol>

                <h4 class="font-bold text-lg mt-6">Code Standards</h4>
                <ul class="list-disc ml-6">
                    <li>Follow PSR-12 coding standards</li>
                    <li>Use type hints for all parameters and return types</li>
                    <li>Document complex methods with PHPDoc</li>
                    <li>Use meaningful variable names (avoid $a, $b, $x)</li>
                    <li>Keep methods small (< 50 lines ideally)</li>
                    <li>Use Laravel's helper functions (now(), auth(), etc.)</li>
                </ul>

                <h4 class="font-bold text-lg mt-6">Database Migrations</h4>
                <ul class="list-disc ml-6">
                    <li>Always add foreign key constraints</li>
                    <li>Use cascadeOnDelete() or nullOnDelete() appropriately</li>
                    <li>Add indexes for frequently queried columns</li>
                    <li>Use softDeletes() for data that should be recoverable</li>
                    <li>Never edit existing migrations - create new ones</li>
                </ul>

                <h4 class="font-bold text-lg mt-6">Security Best Practices</h4>
                <ul class="list-disc ml-6">
                    <li>Use Filament policies for authorization</li>
                    <li>Validate all user inputs</li>
                    <li>Sanitize file uploads (check MIME type)</li>
                    <li>Use CSRF protection (automatic in Filament)</li>
                    <li>Never expose sensitive data in API responses</li>
                    <li>Use Auth::id() instead of auth()->id() for consistency</li>
                </ul>
            </div>
        </x-filament::section>

        {{-- Deployment Checklist --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">🚀 Deployment Checklist</x-slot>

            <div class="space-y-3">
                <h4 class="font-bold text-lg">Pre-Deployment</h4>
                <ul class="list-disc ml-6">
                    <li>✓ Run all tests: <code>php artisan test</code></li>
                    <li>✓ Check for errors: <code>php artisan optimize</code></li>
                    <li>✓ Optimize autoloader: <code>composer dump-autoload --optimize</code></li>
                    <li>✓ Clear all caches: <code>php artisan optimize:clear</code></li>
                    <li>✓ Backup database</li>
                    <li>✓ Review environment variables (.env)</li>
                </ul>

                <h4 class="font-bold text-lg mt-4">Deployment Steps</h4>
                <ol class="list-decimal ml-6">
                    <li>Pull latest code from repository</li>
                    <li>Run migrations: <code>php artisan migrate --force</code></li>
                    <li>Clear & cache configs: <code>php artisan config:cache</code></li>
                    <li>Cache routes: <code>php artisan route:cache</code></li>
                    <li>Cache views: <code>php artisan view:cache</code></li>
                    <li>Link storage: <code>php artisan storage:link</code></li>
                    <li>Restart queue workers if using queues</li>
                    <li>Test critical features</li>
                </ol>

                <h4 class="font-bold text-lg mt-4">Post-Deployment</h4>
                <ul class="list-disc ml-6">
                    <li>Monitor error logs</li>
                    <li>Check application performance</li>
                    <li>Verify file uploads working</li>
                    <li>Test user authentication</li>
                    <li>Validate database connections</li>
                </ul>
            </div>
        </x-filament::section>

        {{-- Common Issues & Solutions --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">🔧 Common Issues & Solutions</x-slot>

            <div class="space-y-3">
                <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <p class="font-semibold">Issue: "Class not found" error</p>
                    <p class="text-sm mt-1"><strong>Solution:</strong> Run <code>composer dump-autoload</code></p>
                </div>

                <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <p class="font-semibold">Issue: Changes not reflected after editing Filament resource</p>
                    <p class="text-sm mt-1"><strong>Solution:</strong> Run <code>php artisan filament:cache-components</code> and clear browser cache</p>
                </div>

                <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <p class="font-semibold">Issue: File upload not working</p>
                    <p class="text-sm mt-1"><strong>Solution:</strong> Check storage is symlinked: <code>php artisan storage:link</code></p>
                </div>

                <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <p class="font-semibold">Issue: Foreign key constraint error in migration</p>
                    <p class="text-sm mt-1"><strong>Solution:</strong> Ensure referenced tables are created first, check data types match</p>
                </div>

                <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <p class="font-semibold">Issue: Unauthorized access to panel</p>
                    <p class="text-sm mt-1"><strong>Solution:</strong> Check user has correct role/permission via Spatie Permission package</p>
                </div>
            </div>
        </x-filament::section>

        {{-- Contact & Support --}}
        <x-filament::section>
            <x-slot name="heading">📞 Developer Support</x-slot>

            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <h5 class="font-bold text-blue-900 dark:text-blue-100">Documentation</h5>
                    <ul class="text-sm space-y-1 mt-2">
                        <li><a href="https://laravel.com/docs" target="_blank" class="text-blue-600 hover:underline">Laravel Docs</a></li>
                        <li><a href="https://filamentphp.com/docs" target="_blank" class="text-blue-600 hover:underline">Filament Docs</a></li>
                        <li><a href="https://spatie.be/docs/laravel-permission" target="_blank" class="text-blue-600 hover:underline">Spatie Permission</a></li>
                    </ul>
                </div>

                <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <h5 class="font-bold text-green-900 dark:text-green-100">Code Repository</h5>
                    <ul class="text-sm space-y-1 mt-2">
                        <li>Repository: [Your Git URL]</li>
                        <li>Branch: main/production</li>
                        <li>Version: v1.0.0</li>
                    </ul>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
