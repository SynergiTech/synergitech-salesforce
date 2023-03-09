<?php

namespace SynergiTech\Salesforce\Services;

use Illuminate\Support\Collection;
use Omniphx\Forrest\Exceptions\SalesforceException;
use Omniphx\Forrest\Providers\Laravel\Facades\Forrest;
use SynergiTech\Salesforce\Exceptions\InvalidFieldException;
use SynergiTech\Salesforce\Exceptions\MalformedQueryException;
use SynergiTech\Salesforce\Exceptions\NotFoundException;
use SynergiTech\Salesforce\Models\Builder;
use SynergiTech\Salesforce\Models\Response;

class TableService extends Builder
{
    /**
     * Retrieves one or more available objects from Salesforce by Id
     *
     * @param int|string $id
     * @return array<mixed>
     */
    public function find(int|string $id, string $fieldName = 'Id'): array
    {
        $this->where($fieldName, $id);
        $response = $this->get();

        if ($response->records->count() === 0) {
            throw new NotFoundException("A record with the ID '{$id}' could not be found");
        }

        /** @var array<mixed> $result */
        $result = $response->records->first();

        return $result;
    }

    /**
     * Retrieves one or more available objects from Salesforce by Id
     *
     * @param int|string|array<int|string> $id
     * @return Collection<int, mixed>
     */
    public function findMany(int|string|array $id, string $fieldName = 'Id'): Collection
    {
        $id = is_array($id) ? $id : [$id];
        $this->whereIn($fieldName, $id);
        $response = $this->get();

        if ($response->records->count() === 0) {
            throw new NotFoundException("No records with the specified IDs could be found");
        }

        return $response->records;
    }

    public function get(): Response
    {
        try {
            $query = $this->getQuery();
            return new Response($query, Forrest::query($query));
        } catch (SalesforceException $ex) {
            $message = $ex->getMessage();
            /** @var array<mixed> $errors */
            $errors = json_decode($message);
            /** @var \stdClass{errorCode:string, message:string} $error */
            $error = $errors[0];

            if (property_exists($error, 'errorCode')) {
                $message = $this->formatErrorMessage($error->message);

                switch ($error->errorCode) {
                    case 'MALFORMED_QUERY':
                        throw new MalformedQueryException($message);
                    case 'INVALID_QUERY_FILTER_OPERATOR':
                        throw new InvalidFieldException($message);
                }
            }

            throw $ex;
        }
    }

    protected function formatErrorMessage(string $message): string
    {
        return str_replace('\n', ' - ', $message);
    }
}
