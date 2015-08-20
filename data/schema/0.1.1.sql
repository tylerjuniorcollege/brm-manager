BEGIN;
CREATE TABLE "adminer_brm_content_version" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "brmid" integer NULL,
  "brmversionid" integer NULL,
  "userid" integer NOT NULL,
  "subject" text NULL,
  "content" text NULL,
  "created" integer NOT NULL,
  FOREIGN KEY ("userid") REFERENCES "user" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION,
  FOREIGN KEY ("brmid") REFERENCES "brm_campaigns" ("id") ON DELETE CASCADE ON UPDATE NO ACTION
);
INSERT INTO "adminer_brm_content_version" ("id", "brmid", "brmversionid", "userid", "content", "created") SELECT "id", "brmid", "brmversionid", "userid", "content", "created" FROM "brm_content_version";
DROP TABLE "brm_content_version";
ALTER TABLE "adminer_brm_content_version" RENAME TO "brm_content_version";
CREATE TRIGGER "brm_content_version_ai" AFTER    INSERT ON "brm_content_version"
BEGIN
  UPDATE "brm_content_version" SET "brmversionid" = (SELECT COUNT(id) FROM "brm_content_version" WHERE "brmid" = NEW.brmid) WHERE "id" = NEW.id;
END;
COMMIT;