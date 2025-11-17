# Task Scoring System Documentation

## Overview
The task scoring system provides comprehensive evaluation of employee performance based on task completion, quality, timeliness, and other factors. Scores are calculated both at the individual task level and aggregated for period-based evaluations (monthly, quarterly, annually).

## How Task Filtering Works for Period Evaluations

### Key Principle
**When evaluating tasks for a specific period (month/quarter), we include tasks that were:**
1. **Assigned during that period** (based on `assigned_at` date)
2. **OR have a due date within that period** (based on `due_date`)

### Why This Approach?
This ensures we capture all tasks relevant to the evaluation period:
- Tasks assigned this month but due next month are counted
- Tasks assigned last month but due this month are counted
- This gives a complete picture of the user's workload and performance for the period

### Example: Monthly Evaluation for March 2025
```php
// This query includes:
// 1. Tasks assigned between March 1-31, 2025
// 2. Tasks with due dates between March 1-31, 2025
$tasks = Task::forMonth(2025, 3)->get();
```

**Included tasks:**
- Task A: Assigned March 5, due March 20 ✓ (assigned in March)
- Task B: Assigned February 28, due March 15 ✓ (due in March)
- Task C: Assigned March 25, due April 5 ✓ (assigned in March)
- Task D: Assigned February 10, due February 25 ✗ (neither assigned nor due in March)

## Individual Task Score Calculation

### Score Components

#### 1. Base Score (0-10 points)
- **Completed task**: +10 points
- **In progress/Working on**: +5 points
- **Pending/Assigned**: 0 points

#### 2. Email Confirmation Timing (±3 points)
- **On-time email** (sent by due date): +3 points
- **Late email** (sent after due date): -2 points
- **No email sent**: 0 points (no bonus, no penalty)

#### 3. Priority Bonus (0-6 points)
Based on task priority:
- **Critical**: +6 points
- **Urgent**: +5 points
- **High**: +3 points
- **Medium**: +2 points
- **Normal**: +1 point
- **Low**: 0 points

#### 4. Quality Bonus (+2 points)
- Awarded if task completed **without rejection**
- Encourages first-time quality work

#### 5. Overdue Penalty (-5 points)
- Applied if task is currently overdue
- A task is overdue if:
  - Due date has passed AND
  - Task is not completed AND
  - No email confirmation has been sent

#### 6. Rejection Penalty (-8 points)
- Applied if task status is "rejected"
- Significant penalty to encourage quality submissions

#### 7. Experience Multiplier (1.0x - 1.4x)
Based on total tasks completed by user:
- **0-5 tasks**: 1.0x (New)
- **6-15 tasks**: 1.1x (Beginner)
- **16-30 tasks**: 1.2x (Experienced)
- **31-50 tasks**: 1.3x (Veteran)
- **51+ tasks**: 1.4x (Expert)

### Score Calculation Formula
```
raw_score = base_score + email_timing + priority_bonus + quality_bonus
            + overdue_penalty + rejection_penalty

final_score = max(0, raw_score * experience_multiplier)
```

### Example Task Score
```
Task Details:
- Status: Completed
- Priority: High
- Email sent: On time
- No rejections
- User experience: 25 tasks (Experienced)

Calculation:
  Base: 10 (completed)
  Email: +3 (on time)
  Priority: +3 (high)
  Quality: +2 (no rejection)
  Overdue: 0 (not overdue)
  Rejection: 0 (not rejected)
  Raw Score: 18
  Experience Multiplier: 1.2x
  Final Score: 18 × 1.2 = 21.6 points
```

## Admin-Closed Tasks

When an admin closes a task using the admin-close feature:
- The task score is **calculated and stored** in `final_score` field
- The score is **preserved** for future evaluations
- For overdue tasks that are cancelled, the score reflects the state before cancellation
- This ensures consistent scoring even after administrative actions

## Period-Based Evaluation (Monthly/Quarterly)

### Aggregated Period Score

The system calculates a comprehensive score for all tasks in a period:

```php
// Get monthly score for a user
$scoringService = new TaskScoringService();
$monthlyScore = $scoringService->calculateMonthlyScore($user, 2025, 3);

// Get quarterly score for a user
$quarterlyScore = $scoringService->calculateQuarterlyScore($user, 2025, 1);
```

### Period Score Components

#### 1. Total Score
Sum of all individual task scores in the period

#### 2. Average Score
Total score divided by number of tasks

#### 3. Completion Rate
Percentage of tasks completed in the period

#### 4. Score Breakdown
- **Total Base Score**: Sum of all completion/progress scores
- **Total Priority Bonus**: Sum of all priority bonuses
- **Total Quality Bonus**: Sum of all quality bonuses
- **Total Penalties**: Sum of all penalties (overdue + rejection + late email)

### Example Monthly Evaluation
```
User: John Doe
Period: March 2025

Tasks in Period: 12
- 5 assigned in March, due in March
- 3 assigned in February, due in March
- 4 assigned in March, due in April

Results:
  Total Score: 156.8 points
  Average Score: 13.07 points per task
  Completion Rate: 83.33% (10/12 completed)

  Breakdown:
    Base Scores: 95 points
    Priority Bonuses: 42 points
    Quality Bonuses: 20 points
    Penalties: -15 points (3 overdue tasks)
```

## Performance Metrics Integration

### PerformanceMetric Model
Stores calculated metrics for each period:
- Tasks assigned/completed
- On-time rate
- Quality score
- Efficiency score
- Overall performance score

### UserEvaluationService
Provides comprehensive user evaluation:
- Normalized scores (compared to team average)
- Letter grades (A+ to D)
- Rankings
- Team comparison

### PerformanceCalculator
Generates formal evaluations:
- Monthly evaluations
- Quarterly evaluations
- Annual evaluations
- Rankings calculation

## Usage Examples

### 1. Calculate Individual Task Score
```php
$scoringService = new TaskScoringService();
$task = Task::find(123);
$user = $task->assignee;

$scoreData = $scoringService->calculateTaskScore($task, $user);

echo "Score: " . $scoreData['score'];
echo "Breakdown: " . print_r($scoreData['breakdown'], true);
```

### 2. Get Monthly Scores for User
```php
$scoringService = new TaskScoringService();
$user = User::find(1);

$monthlyScore = $scoringService->calculateMonthlyScore($user, 2025, 3);

echo "Total Score: " . $monthlyScore['total_score'];
echo "Average Score: " . $monthlyScore['average_score'];
echo "Completion Rate: " . $monthlyScore['completion_rate'] . "%";
```

### 3. Get Quarterly Evaluation
```php
$performanceCalc = new PerformanceCalculator();
$evaluation = $performanceCalc->generateQuarterlyEvaluation(userId: 1, year: 2025, quarter: 1);

echo "Performance Score: " . $evaluation->performance_score;
echo "Tasks Completed: " . $evaluation->tasks_completed;
echo "Quality Score: " . $evaluation->quality_score;
```

### 4. Compare User to Team Average
```php
$evaluationService = new UserEvaluationService();
$user = User::find(1);

$evaluation = $evaluationService->calculateUserEvaluation($user, 'month');

echo "Overall Score: " . $evaluation['overall_score'];
echo "Grade: " . $evaluation['grade'];
echo "Rank: " . $evaluation['rank'];
echo "Team Average Completion Rate: " . $evaluation['team_averages']['completion_rate'];
```

## Database Schema

### Tasks Table (New Fields)
```sql
- final_score (decimal 8,2): Preserved score when admin closes task
- closed_by (foreign key): Admin who closed the task
- closed_at (timestamp): When task was closed
- closure_notes (text): Admin notes on closure
```

### Usage in Queries

#### Get tasks for March 2025
```php
Task::forMonth(2025, 3)->get();
```

#### Get tasks for Q1 2025
```php
Task::forQuarter(2025, 1)->get();
```

#### Get tasks for specific period
```php
$start = Carbon::parse('2025-03-01');
$end = Carbon::parse('2025-03-31');
Task::forPeriod($start, $end)->get();
```

## Best Practices

### 1. For Monthly Evaluations
- Use `calculateMonthlyScore()` to get aggregated task scores
- Use `generateMonthlyEvaluation()` to store formal evaluation
- Always specify year and month explicitly

### 2. For Quarterly Evaluations
- Use `calculateQuarterlyScore()` for Q1-Q4
- Quarter 1 = Jan-Mar, Q2 = Apr-Jun, Q3 = Jul-Sep, Q4 = Oct-Dec
- Store results in EmployeeEvaluation table

### 3. For Admin Actions
- Use `adminCloseTask()` to close/cancel tasks
- Score is automatically preserved in `final_score`
- Overdue tasks are automatically cancelled

### 4. For Fair Comparisons
- Use UserEvaluationService for normalized scores
- Compare users against team averages
- Consider experience multiplier in evaluations

## Scoring Philosophy

The scoring system is designed to:
1. **Reward completion**: Base score for finishing tasks
2. **Encourage timeliness**: Bonuses for on-time delivery
3. **Promote quality**: Bonuses for first-time acceptance
4. **Consider difficulty**: Priority bonuses for harder tasks
5. **Penalize delays**: Penalties for overdue work
6. **Discourage rework**: Rejection penalties
7. **Recognize experience**: Multipliers for veteran users
8. **Ensure fairness**: Team-normalized comparisons

The system ensures that all relevant tasks for a period are included, whether they were assigned or due in that timeframe, providing a complete and fair evaluation.
