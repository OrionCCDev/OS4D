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

class ProjectProgressExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    protected $projects;

    public function __construct($projects)
    {
        $this->projects = $projects;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = collect();

        foreach ($this->projects as $project) {
            // Add project summary row
            $data->push([
                'Project Code' => $project['short_code'],
                'Project Name' => $project['name'],
                'Status' => ucfirst($project['status']),
                'Owner' => $project['owner'],
                'Total Tasks' => $project['total_tasks'],
                'Completed' => $project['completed_tasks'],
                'In Progress' => $project['in_progress_tasks'],
                'Pending' => $project['pending_tasks'],
                'Overdue' => $project['overdue_tasks'],
                'Completion %' => $project['completion_percentage'] . '%',
                'On-Time Rate %' => $project['on_time_completion_rate'] . '%',
                'Team Size' => $project['team_size'],
                'Due Date' => $project['due_date'] ? \Carbon\Carbon::parse($project['due_date'])->format('Y-m-d') : 'N/A',
                'Days Remaining' => $project['days_remaining'] ?? 'N/A',
                'Risk Status' => $project['is_at_risk'] ? 'At Risk' : 'On Track',
            ]);

            // Add team performance for this project
            if (count($project['team_performance']) > 0) {
                $data->push(['', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
                $data->push(['', 'Team Member Performance:', '', '', '', '', '', '', '', '', '', '', '', '', '']);
                $data->push(['', 'Member', 'Total Tasks', 'Completed', 'Pending', 'Overdue', 'Completion Rate', '', '', '', '', '', '', '', '']);

                foreach ($project['team_performance'] as $member) {
                    $data->push([
                        '',
                        $member['user_name'],
                        $member['total_tasks'],
                        $member['completed_tasks'],
                        $member['pending_tasks'],
                        $member['overdue_tasks'],
                        $member['completion_rate'] . '%',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                    ]);
                }
                $data->push(['', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
            }

            // Add separator between projects
            $data->push(['', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
        }

        return $data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Project Code',
            'Project Name',
            'Status',
            'Owner',
            'Total Tasks',
            'Completed',
            'In Progress',
            'Pending',
            'Overdue',
            'Completion %',
            'On-Time Rate %',
            'Team Size',
            'Due Date',
            'Days Remaining',
            'Risk Status',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (headings)
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
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
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Project Progress Report';
    }
}

