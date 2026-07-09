public function submitted()
{
    return $this->state(fn (array $attributes) => [
        'status' => Project::STATUS_SUBMITTED,
        'submission_date' => now(),
    ]);
}