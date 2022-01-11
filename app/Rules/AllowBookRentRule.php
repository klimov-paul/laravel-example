<?php

namespace App\Rules;

use App\Models\Book;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

/**
 * Validates whether particular user can rent particular book, providing verbose error message.
 *
 * Book existence will be checked internally. Use {@see getBook()} to retrieve its instance on validation success.
 *
 * @see \App\Models\Book::allowRent()
 */
class AllowBookRentRule implements Rule
{
    /**
     * @var string validation error message.
     */
    protected $message;

    protected User $user;

    protected ?Book $book;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function passes($attribute, $value): bool
    {
        $this->message = null;

        $this->book = Book::query()->find($value);

        if ($this->book === null) {
            $this->message = __('validation.exists', ['attribute' => $attribute]);

            return false;
        }

        if ($this->user->activeSubscription === null) {
            $this->message = __('You do not have active subscription.');

            return false;
        }

        if (!$this->user->activeSubscription->subscriptionPlan->allowBook($this->book)) {
            $this->message = __('This book is not allowed by your subscription plan.');

            return false;
        }

        if (!$this->user->activeSubscription->subscriptionPlan->hasAvailableRentSlots($this->user)) {
            $this->message = __('Rents limit exceeded.');

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * Returns last queried book model.
     *
     * @return \App\Models\Book|null
     */
    public function getBook(): ?Book
    {
        return $this->book;
    }
}
