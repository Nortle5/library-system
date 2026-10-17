<?php
require_once __DIR__ . '/db.php';
function borrowBook(string $userId, string $bookId): string
{
    try {
        $userOid = new MongoDB\BSON\ObjectId($userId);
        $bookOid = new MongoDB\BSON\ObjectId($bookId);
        $borrowingsCol = getDb()->selectCollection('borrowings');
        $booksCol = getDb()->selectCollection('books');

        if ($borrowingsCol->findOne(['userId' => $userOid, 'bookId' => $bookOid, 'status' => 'borrowed'])) {
            return 'already';
        }

        $taken = $booksCol->updateOne(
            ['_id' => $bookOid, 'availableCopies' => ['$gt' => 0]],
            ['$inc' => ['availableCopies' => -1]]
        );

        if ($taken->getModifiedCount() !== 1) {
            return 'unavailable';
        }

        try {
            $borrowingsCol->insertOne([
                'userId'     => $userOid,
                'bookId'     => $bookOid,
                'borrowedAt' => new MongoDB\BSON\UTCDateTime(),
                'returnedAt' => null,
                'status'     => 'borrowed',
            ]);
            return 'borrowed';
        } catch (Throwable $e) {

            $booksCol->updateOne(['_id' => $bookOid], ['$inc' => ['availableCopies' => 1]]);
            return 'error';
        }
    } catch (Throwable $e) {
        return 'error';
    }
}

function returnBook(string $userId, string $borrowingId): string
{
    try {
        $borrowingsCol = getDb()->selectCollection('borrowings');
        $booksCol = getDb()->selectCollection('books');
        $recordId = new MongoDB\BSON\ObjectId($borrowingId);

        $record = $borrowingsCol->findOneAndUpdate(
            ['_id' => $recordId, 'userId' => new MongoDB\BSON\ObjectId($userId), 'status' => 'borrowed'],
            ['$set' => ['status' => 'returned', 'returnedAt' => new MongoDB\BSON\UTCDateTime()]]
        );

        if (!$record) {
            return 'notfound';
        }

        try {
            $booksCol->updateOne(['_id' => $record['bookId']], ['$inc' => ['availableCopies' => 1]]);
            return 'returned';
        } catch (Throwable $e) {
            // Couldn't put the copy back, so undo the return.
            $borrowingsCol->updateOne(
                ['_id' => $recordId],
                ['$set' => ['status' => 'borrowed', 'returnedAt' => null]]
            );
            return 'error';
        }
    } catch (Throwable $e) {
        return 'error';
    }
}