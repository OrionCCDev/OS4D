<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Overdue Task Reminder</title>
    <style>
      body {
        font-family: "Segoe UI", Helvetica, Arial, sans-serif;
        background: #f8fafc;
        color: #1f2937;
        margin: 0;
        padding: 0;
      }
      .wrapper {
        max-width: 640px;
        margin: 0 auto;
        padding: 32px 24px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 16px 48px rgba(15, 23, 42, 0.08);
      }
      h1 {
        margin-top: 0;
        color: #dc2626;
        font-size: 1.4rem;
      }
      .meta {
        list-style: none;
        padding: 0;
        margin: 20px 0;
      }
      .meta li {
        margin-bottom: 8px;
      }
      .button {
        display: inline-block;
        background: #2563eb;
        color: #ffffff !important;
        padding: 12px 18px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        margin-top: 16px;
      }
      .footer {
        margin-top: 32px;
        font-size: 0.85rem;
        color: #6b7280;
      }
      .message-box {
        margin-top: 24px;
        padding: 16px;
        border-left: 4px solid #2563eb;
        background: #eff6ff;
        border-radius: 8px;
      }
    </style>
  </head>
  <body>
    <div class="wrapper">
      <h1>Overdue Task Reminder</h1>
      <p>Hi {{ optional($task->assignee)->name ?? 'there' }},</p>

      <p>The task <strong>"{{ $task->title }}"</strong> assigned to you is currently overdue. Please review the details below and take action as soon as possible.</p>

      <ul class="meta">
        <li><strong>Task:</strong> {{ $task->title }}</li>
        @if($task->project)
          <li><strong>Project:</strong> {{ $task->project->name }} @if($task->project->short_code) ({{ $task->project->short_code }}) @endif</li>
        @endif
        <li><strong>Due date:</strong> {{ optional($task->due_date)->format('M d, Y') ?? 'Not set' }}</li>
        <li><strong>Overdue for:</strong> {{ $overdueDuration }}</li>
        <li><strong>Current status:</strong> {{ str_replace('_', ' ', ucfirst($task->status ?? 'unknown')) }}</li>
      </ul>

      @if(!empty($customMessage))
        <div class="message-box">
          {!! nl2br(e($customMessage)) !!}
        </div>
      @else
        <p>Please update the task with the latest progress, and let us know if you need any assistance or additional time.</p>
      @endif

      <p>
        <a href="{{ route('tasks.show', $task->id) }}" class="button">View Task Details</a>
      </p>

      <p class="footer">
        Sent by {{ $manager->name }} via Orion Designers task platform.
        <br>
        Thank you for your prompt attention.
      </p>
    </div>
  </body>
</html>

