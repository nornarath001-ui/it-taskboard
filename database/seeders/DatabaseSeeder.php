<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $make = fn (string $name, string $email, string $role, string $title) => User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'role' => $role, 'job_title' => $title, 'password' => 'password'],
        );

        $admin   = $make('Sophea Admin',   'admin@it.local',   'admin',   'IT Director');
        $manager = $make('Vannak Manager', 'manager@it.local', 'manager', 'IT Operations Manager');
        $staff   = $make('Dara Staff',     'staff@it.local',   'staff',   'Support Engineer');
        $sokha   = $make('Sokha Chan',     'sokha@it.local',   'staff',   'Network Engineer');
        $rithy   = $make('Rithy Sok',      'rithy@it.local',   'staff',   'Systems Administrator');

        if (Task::count() > 0) {
            return;
        }

        $rows = [
            ['Replace failed disk in NAS-02',           'hardware', 'critical', 'in_progress',  0, $rithy],
            ['Reset MFA for new finance hire',          'security', 'high',     'todo',         0, $staff],
            ['Fix Wi-Fi drops on 3rd floor',            'network',  'high',     'in_progress',  0, $sokha],
            ['Renew SSL certificate for staff portal',  'servers',  'medium',   'todo',         0, $rithy],
            ['Set up laptop for incoming HR assistant', 'helpdesk', 'medium',   'todo',         0, $staff],
            ['Patch Windows servers (monthly cycle)',   'servers',  'high',     'todo',        -1, $rithy],
            ['Review firewall rules after audit',       'security', 'high',     'todo',        -2, $sokha],
            ['Install Adobe licences for design team',  'software', 'low',      'done',        -1, $staff],
            ['Replace projector bulb in meeting room',  'hardware', 'low',      'todo',         1, $staff],
            ['Document backup restore procedure',       'servers',  'medium',   'todo',         2, $rithy],
            ['Rack and label new access switch',        'network',  'medium',   'todo',         3, $sokha],
        ];

        foreach ($rows as [$title, $category, $priority, $status, $offset, $assignee]) {
            Task::create([
                'title'        => $title,
                'description'  => null,
                'category'     => $category,
                'priority'     => $priority,
                'status'       => $status,
                'due_date'     => today()->addDays($offset),
                'assigned_to'  => $assignee->id,
                'created_by'   => $manager->id,
                'completed_at' => $status === 'done' ? now() : null,
            ]);
        }
    }
}
