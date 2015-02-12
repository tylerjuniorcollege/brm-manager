-- Adminer 4.1.0 SQLite 3 dump

DROP TABLE IF EXISTS "brm_auth_list";
CREATE TABLE "brm_auth_list" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "userid" integer NOT NULL,
  "brmid" integer NOT NULL,
  "versionid" integer NOT NULL,
  "permission" integer NOT NULL,
  "approved" integer NOT NULL DEFAULT '0',
  "comments" text NULL,
  "firstviewed" integer NULL,
  "timestamp" integer NOT NULL,
  FOREIGN KEY ("versionid") REFERENCES "brm_content_version" ("id") ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY ("brmid") REFERENCES "brm_campaigns" ("id") ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY ("userid") REFERENCES "user" ("id") ON DELETE CASCADE ON UPDATE NO ACTION
);

INSERT INTO "brm_auth_list" ("id", "userid", "brmid", "versionid", "permission", "approved", "comments", "firstviewed", "timestamp") VALUES (1, 2,  1,  1,  3,  '0',  NULL, NULL, '');
INSERT INTO "brm_auth_list" ("id", "userid", "brmid", "versionid", "permission", "approved", "comments", "firstviewed", "timestamp") VALUES (2, 3,  1,  1,  3,  '0',  NULL, NULL, '');
INSERT INTO "brm_auth_list" ("id", "userid", "brmid", "versionid", "permission", "approved", "comments", "firstviewed", "timestamp") VALUES (3, 2,  1,  2,  3,  -1, NULL, 1423002500, 1423002341);
INSERT INTO "brm_auth_list" ("id", "userid", "brmid", "versionid", "permission", "approved", "comments", "firstviewed", "timestamp") VALUES (4, 3,  1,  2,  3,  1,  NULL, 1423002341, 1423002341);
INSERT INTO "brm_auth_list" ("id", "userid", "brmid", "versionid", "permission", "approved", "comments", "firstviewed", "timestamp") VALUES (5, 2,  1,  3,  3,  '0',  NULL, 1423002341, 1423002341);

DROP TABLE IF EXISTS "brm_campaigns";
CREATE TABLE "brm_campaigns" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "title" text NOT NULL,
  "description" text NOT NULL,
  "current_version" integer NOT NULL,
  "templateid" text NOT NULL,
  "state" integer NOT NULL DEFAULT '0',
  "createdby" integer NOT NULL,
  "created" integer NOT NULL,
  FOREIGN KEY ("current_version") REFERENCES "brm_content_version" ("id") ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY ("createdby") REFERENCES "user" ("id") ON DELETE CASCADE ON UPDATE NO ACTION
);

INSERT INTO "brm_campaigns" ("id", "title", "description", "current_version", "templateid", "state", "createdby", "created") VALUES (1, 'TEST BRM', 'Testing BRM',  3,  '', '0',  1,  1422916676);

DROP TABLE IF EXISTS "brm_content_version";
CREATE TABLE "brm_content_version" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "brmid" integer NULL,
  "brmversionid" integer NULL,
  "content" text NOT NULL,
  "created" integer NOT NULL,
  FOREIGN KEY ("brmid") REFERENCES "brm_campaigns" ("id") ON DELETE CASCADE ON UPDATE NO ACTION
);

INSERT INTO "brm_content_version" ("id", "brmid", "content", "created") VALUES (1,  1,  'THIS IS TEST CONTENT!!!',  1422916676);
INSERT INTO "brm_content_version" ("id", "brmid", "content", "created") VALUES (2,  1,  'MOAR CONTENT HERE!!!!',  1423002341);
INSERT INTO "brm_content_version" ("id", "brmid", "content", "created") VALUES (3,  1,  'Another Version Here', 1422995305);

DELIMITER ;;
CREATE TRIGGER "brm_content_version_ai" AFTER INSERT ON "brm_content_version" FOR EACH ROW
BEGIN
UPDATE "brm_content_version" SET "brmversionid" = (SELECT COUNT(id) FROM "brm_content_version" WHERE "brmid" = NEW.brmid) WHERE "id" = NEW.id;
END;;

DELIMITER ;


DROP TABLE IF EXISTS "brm_header_images";
CREATE TABLE "brm_header_images" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "brmid" integer NOT NULL,
  "brmversionid" integer NOT NULL,
  "filename" text NOT NULL,
  "created" integer NOT NULL,
  "uploadedby" integer NOT NULL,
  FOREIGN KEY ("brmid") REFERENCES "brm_campaigns" ("id") ON DELETE CASCADE,
  FOREIGN KEY ("brmversionid") REFERENCES "brm_content_version" ("id") ON DELETE CASCADE,
  FOREIGN KEY ("uploadedby") REFERENCES "user" ("id") ON DELETE CASCADE
);


DROP TABLE IF EXISTS "comments";
CREATE TABLE "comments" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "userid" integer NOT NULL,
  "brmid" integer NOT NULL,
  "versionid" integer NOT NULL,
  "comment" text NOT NULL,
  "timestamp" integer NOT NULL,
  FOREIGN KEY ("userid") REFERENCES "user" ("id") ON DELETE CASCADE,
  FOREIGN KEY ("brmid") REFERENCES "brm_campaigns" ("id") ON DELETE CASCADE,
  FOREIGN KEY ("versionid") REFERENCES "brm_content_version" ("id") ON DELETE CASCADE
);

INSERT INTO "comments" ("id", "userid", "brmid", "versionid", "comment", "timestamp") VALUES (1,  1,  1,  1,  'This is a Comment',  1422995305);
INSERT INTO "comments" ("id", "userid", "brmid", "versionid", "comment", "timestamp") VALUES (2,  1,  1,  3,  'Testing Comments ...', 1423072717);
INSERT INTO "comments" ("id", "userid", "brmid", "versionid", "comment", "timestamp") VALUES (3,  1,  1,  3,  'More Comments!', 1423086096);

DROP TABLE IF EXISTS "login_attempts";
CREATE TABLE "login_attempts" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "userid" integer NOT NULL,
  "timestamp" integer NOT NULL,
  "hash" text NOT NULL,
  "emailid" text NULL,
  "result" integer NOT NULL DEFAULT '0',
  FOREIGN KEY ("userid") REFERENCES "user" ("id") ON DELETE CASCADE ON UPDATE NO ACTION
);

DROP TABLE IF EXISTS "sqlite_sequence";
CREATE TABLE sqlite_sequence(name,seq);

INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('user', '5');
INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('login_attempts', '50');
INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('brm_content_version',  '3');
INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('comments', '3');
INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('brm_auth_list',  '5');
INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('brm_campaigns',  '1');

DROP TABLE IF EXISTS "user";
CREATE TABLE "user" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "firstname" text NULL,
  "lastname" text NULL,
  "email" text NOT NULL,
  "permissions" integer NOT NULL DEFAULT '1'
);


DROP VIEW IF EXISTS "view_approved";
CREATE TABLE "view_approved" ("brmid" integer, "count" );


DROP VIEW IF EXISTS "view_auth_list";
CREATE TABLE "view_auth_list" ("id" integer, "title" text, "description" text, "current_version" integer, "templateid" text, "state" integer, "createdby" integer, "created" integer, "version_count" , "version_created" , "auth_user" integer, "auth_permission" integer, "auth_approved" integer);


DROP VIEW IF EXISTS "view_brm_comments";
CREATE TABLE "view_brm_comments" ("userid" integer, "brmid" integer, "versionid" integer, "comment" text, "timestamp" integer, "useremail" text, "userfirstname" text, "userlastname" text);


DROP VIEW IF EXISTS "view_brm_list";
CREATE TABLE "view_brm_list" ("id" integer, "title" text, "description" text, "current_version" integer, "templateid" text, "state" integer, "createdby" integer, "created" integer, "version_count" , "version_created" );


DROP VIEW IF EXISTS "view_brm_list_approve";
CREATE TABLE "view_brm_list_approve" ("id" integer, "title" text, "description" text, "current_version" integer, "templateid" text, "state" integer, "createdby" integer, "created" integer, "version_count" , "version_created" , "approval_needed" , "approved" , "denied" );


DROP VIEW IF EXISTS "view_common_users";
CREATE TABLE "view_common_users" ("userid" integer, "firstname" text, "lastname" text, "email" text, "counter" );


DROP VIEW IF EXISTS "view_deny_approval";
CREATE TABLE "view_deny_approval" ("brmid" integer, "count" );


DROP VIEW IF EXISTS "view_need_approval";
CREATE TABLE "view_need_approval" ("brmid" integer, "count" );


DROP TABLE IF EXISTS "view_approved";
CREATE VIEW "view_approved" AS
SELECT "auth_list"."id" AS "brmid", COUNT("auth_list"."auth_approved") AS "count" FROM "view_auth_list" AS "auth_list" WHERE "auth_list"."auth_approved" = 1;

DROP TABLE IF EXISTS "view_auth_list";
CREATE VIEW "view_auth_list" AS
SELECT "cbrm_list".*, "auth"."userid" AS "auth_user",
"auth"."permission" AS "auth_permission", "auth"."approved" AS "auth_approved" 
FROM "view_brm_list" AS "cbrm_list"
LEFT JOIN "brm_auth_list" AS "auth" ON "cbrm_list"."id" = "auth"."brmid" AND "cbrm_list"."current_version" = "auth"."versionid";

DROP TABLE IF EXISTS "view_brm_comments";
CREATE VIEW "view_brm_comments" AS
SELECT "brm_auth"."userid" AS "userid", 
     "brm_auth"."brmid" AS "brmid", 
     "brm_auth"."versionid" AS "versionid", 
     "brm_auth"."comments" AS "comment", 
     "brm_auth"."timestamp" AS "timestamp",
     "user"."email" AS "useremail",
     "user"."firstname" AS "userfirstname",
     "user"."lastname" AS "userlastname"
FROM "brm_auth_list" AS "brm_auth" 
LEFT JOIN "user" AS "user" ON "brm_auth"."userid" = "user"."id"
WHERE "brm_auth"."comments" IS NOT NULL
UNION
SELECT "c"."userid" AS "userid", 
     "c"."brmid" AS "brmid", 
     "c"."versionid" AS "versionid", 
     "c"."comment" AS "comment", 
     "c"."timestamp" AS "timestamp",
    "user"."email" AS "useremail",
     "user"."firstname" AS "userfirstname",
     "user"."lastname" AS "userlastname"
FROM "comments" AS "c"
LEFT JOIN "user" AS "user" ON "c"."userid" = "user"."id"
WHERE "c"."comment" IS NOT NULL;

DROP TABLE IF EXISTS "view_brm_list";
CREATE VIEW "view_brm_list" AS
SELECT "brm".*, COUNT("brm_versions"."id") AS "version_count", MAX("brm_versions"."created") AS "version_created" FROM "brm_campaigns" AS "brm"
LEFT JOIN "brm_content_version" AS "brm_versions" ON "brm"."id" = "brm_versions"."brmid";

DROP TABLE IF EXISTS "view_brm_list_approve";
CREATE VIEW "view_brm_list_approve" AS
SELECT "brm_list". *, "approval_needed"."count" AS "approval_needed", "approved"."count" AS "approved", "denied"."count" AS "denied" FROM "view_brm_list" AS "brm_list"
LEFT JOIN "view_need_approval" AS "approval_needed" ON "brm_list"."id" = "approval_needed"."brmid"
LEFT JOIN "view_approved" AS "approved" ON "brm_list"."id" = "approved"."brmid"
LEFT JOIN "view_deny_approval" AS "denied" ON "brm_list"."id" = "denied"."brmid";

DROP TABLE IF EXISTS "view_common_users";
CREATE VIEW "view_common_users" AS
SELECT "user"."id" AS "userid", "user"."firstname" AS "firstname", "user"."lastname" AS "lastname", "user"."email" AS "email",
COUNT("brm_auth_list"."id") AS "counter"
FROM "user"
LEFT JOIN "brm_auth_list" ON "user"."id" = "brm_auth_list"."userid"
GROUP BY "brm_auth_list"."userid"
HAVING "counter" > 0
ORDER BY "counter" DESC
LIMIT 10;

DROP TABLE IF EXISTS "view_deny_approval";
CREATE VIEW "view_deny_approval" AS
SELECT "auth_list"."id" AS "brmid", COUNT("auth_list"."auth_approved") AS "count" FROM "view_auth_list" AS "auth_list" WHERE "auth_list"."auth_approved" = -1;

DROP TABLE IF EXISTS "view_need_approval";
CREATE VIEW "view_need_approval" AS
SELECT "auth_list"."id" AS "brmid", COUNT("auth_list"."auth_approved") AS "count" FROM "view_auth_list" AS "auth_list" WHERE "auth_list"."auth_approved" = 0;

-- 