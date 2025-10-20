<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class UserPerformanceExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    protected $userReport;

    public function __construct($userReport)
    {
        $this->userReport = $userReport;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = collect();

        // Add user information
        $data->push([
            'User Information',
            '',
            '',
            '',
            '',
            '',
        ]);
        $data->push([
            'Name:',
            $this->userReport['user']['name'],
            '',
            '',
            '',
            '',
        ]);
        $data->push([
            'Email:',
            $this->userReport['user']['email'],
            '',
            '',
            '',
            '',
        ]);
        if (isset($this->userReport['user']['position'])) {
            $data->push([
                'Position:',
                $this->userReport['user']['position'],
                '',
                '',
                '',
                '',
            ]);
        }
        $data->push(['', '', '', '', '', '']); // Empty row

        // Add performance summary
        $data->push([
            'Performance Summary',
            '',
            '',
            '',
            '',
            '',
        ]);
        $data->push([
            'Metric',
            'Value',
            'Percentage',
            '',
            '',
            '',
        ]);
        $data->push([
            'Total Tasks',
            $this->userReport['total_tasks'],
            '',
            '',
            '',
            '',
        ]);
        $data->push([
            'Completed Tasks',
            $this->userReport['completed_tasks'],
            $this->userReport['completion_rate'] . '%',
            '',
            '',
            '',
        ]);
        $data->push([
            'On-Time Tasks',
            $this->userReport['on_time_tasks'],
            $this->userReport['on_time_rate'] . '%',
            '',
            '',
            '',
        ]);
        $data->push([
            'Overdue Tasks',
            $this->userReport['overdue_tasks'],
            '',
            '',
            '',
            '',
        ]);
        $data->push([
            'Average Completion Time',
            $this->userReport['average_completion_time'] > 0 ? number_format($this->userReport['average_completion_time'], 1) . ' days' : '0 days',
            '',
            '',
            '',
            '',
        ]);
        $data->push([
            'Performance Score',
            $this->userReport['performance_score'] . '%',
            '',
            '',
            '',
            '',
        ]);
        $data->push(['', '', '', '', '', '']); // Empty row

        // Add tasks by priority
        if (!empty($this->userReport['tasks_by_priority']) && $this->userReport['tasks_by_priority']->count() > 0) {
            $data->push([
                'Tasks by Priority',
                '',
                '',
                '',
                '',
                '',
            ]);
            $data->push([
                'Priority',
                'Count',
                'Percentage',
                '',
                '',
                '',
            ]);

            foreach (['urgent' => 'Urgent', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $key => $label) {
                $count = $this->userReport['tasks_by_priority']->get($key, 0);
                if ($count > 0) {
                    $percentage = $this->userReport['total_tasks'] > 0
                        ? round(($count / $this->userReport['total_tasks']) * 100, 1)
                        : 0;

                    $data->push([
                        $label,
                        $count,
                        $percentage . '%',
                        '',
                        '',
                        '',
                    ]);
                }
            }
            $data->push(['', '', '', '', '', '']); // Empty row
        }

        // Add performance grade
        $grade = $this->userReport['performance_score'] >= 90 ? 'A+' :
                ($this->userReport['performance_score'] >= 80 ? 'A' :
                ($this->userReport['performance_score'] >= 70 ? 'B+' :
                ($this->userReport['performance_score'] >= 60 ? 'B' :
                ($this->userReport['performance_score'] >= 50 ? 'C' : 'D'))));

        $data->push([
            'Performance Grade',
            '',
            '',
            '',
            '',
            '',
        ]);
        $data->push([
            'Grade:',
            $grade,
            '',
            '',
            '',
            '',
        ]);
        $data->push([
            'Score:',
            $this->userReport['performance_score'] . '%',
            '',
            '',
            '',
            '',
        ]);

        return $data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'User Performance Report - ' . $this->userReport['user']['name'],
            '',
            '',
            '',
            '',
            'Generated: ' . now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (main heading)
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
            // Style section headers
            'A3' => ['font' => ['bold' => true, 'size' => 12]],
            'A8' => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'User Performance';
    }
}

