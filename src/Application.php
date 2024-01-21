<?php

declare(strict_types=1);

namespace ostark\PgConverter;

use ostark\PgConverter\Statement\CreateIndex;
use ostark\PgConverter\Statement\CreateTable;
use ostark\PgConverter\Statement\InsertInto;

class Application
{
    public const DEFAULT_SCHEMA = 'public';


    public function handle()
    {
        $multiline = <<<'EOT'

CREATE TABLE public.addresses (
    id integer NOT NULL,
    "ownerId" integer,
    "countryCode" character varying(255) NOT NULL,
    "administrativeArea" character varying(255),
    locality character varying(255),
    "dependentLocality" character varying(255),
    "postalCode" character varying(255),
    "sortingCode" character varying(255),
    "addressLine1" character varying(255),
    "addressLine2" character varying(255),
    organization character varying(255),
    "organizationTaxId" character varying(255),
    "fullName" character varying(255),
    "firstName" character varying(255),
    "lastName" character varying(255),
    latitude character varying(255),
    longitude character varying(255),
    "dateCreated" timestamp(0) without time zone NOT NULL,
    "dateUpdated" timestamp(0) without time zone NOT NULL
);


--
-- Name: announcements; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.announcements (
    id integer NOT NULL,
    "userId" integer NOT NULL,
    "pluginId" integer,
    heading character varying(255) NOT NULL,
    body text NOT NULL,
    unread boolean DEFAULT true NOT NULL,
    "dateRead" timestamp(0) without time zone,
    "dateCreated" timestamp(0) without time zone NOT NULL
);


--
-- Name: announcements_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.announcements_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: announcements_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.announcements_id_seq OWNED BY public.announcements.id;


--
-- Name: assetindexdata; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.assetindexdata (
    id integer NOT NULL,
    "volumeId" integer NOT NULL,
    uri text,
    size bigint,
    "timestamp" timestamp(0) without time zone,
    "recordId" integer,
    "inProgress" boolean DEFAULT false,
    completed boolean DEFAULT false,
    "dateCreated" timestamp(0) without time zone NOT NULL,
    "dateUpdated" timestamp(0) without time zone NOT NULL,
    uid character(36) DEFAULT '0'::bpchar NOT NULL,
    "sessionId" integer NOT NULL,
    "isDir" boolean DEFAULT false,
    "isSkipped" boolean DEFAULT false
);
EOT;

        $this->handleDump($multiline);
    }

    public function handleDump(string $dump)
    {
        $builder = new Builder(null);

        $converted = [];

        foreach (explode("\n", $dump) as $line) {

            if ($builder->isCollectingMultilines()) {
                $builder->add($line);
            }

            if ($builder->isLastMultiline($line)) {

                $statement = $builder->toString();
                $name = $builder->getName();

                $converted[] = $this->convertStatement($name, $statement);

                $builder->reset();
                continue;
            }

            if (str_starts_with($line, "CREATE TABLE")) {

                $builder = new Builder("CREATE_TABLE");
                $builder->setStopCharacter(");");
                $builder->add($line);

                continue;
            }

            if (str_starts_with($line, "COPY") && str_ends_with($line, "FROM stdin;")) {
                $builder = new Builder("COPY");
                $builder->setStopCharacter("\.");
                $builder->add($line);

                continue;
            }
        }


        print_r($converted);



    }

    private function convertStatement(?string $name, string $statement): string
    {
        return match($name) {
            'CREATE_TABLE' => (new CreateTable($statement))->toSql(),
            'CREATE_INDEX' => (new CreateIndex($statement))->toSql(),
            'COPY' => (new InsertInto($statement))->toSql(),
            default => throw new \Exception("Unsupported Statement: $name"),
        };



    }


}
