-- Adminer 4.2.0 SQLite 3 dump

DROP TABLE IF EXISTS "brm_auth_list";
CREATE TABLE "brm_auth_list" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "userid" integer NOT NULL,
  "brmid" integer NOT NULL,
  "versionid" integer NOT NULL,
  "permission" integer NOT NULL,
  "approved" integer NOT NULL DEFAULT '0',
  "comment" text NULL,
  "timestamp" integer NULL,
  FOREIGN KEY ("userid") REFERENCES "user" ("id") ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY ("brmid") REFERENCES "brm_campaigns" ("id") ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY ("versionid") REFERENCES "brm_content_version" ("id") ON DELETE CASCADE ON UPDATE NO ACTION
);

DELETE FROM "brm_auth_list";
INSERT INTO "brm_auth_list" ("id", "userid", "brmid", "versionid", "permission", "approved", "comment", "timestamp") VALUES (1, 2,  1,  1,  3,  '0',  NULL, '');
INSERT INTO "brm_auth_list" ("id", "userid", "brmid", "versionid", "permission", "approved", "comment", "timestamp") VALUES (2, 3,  1,  1,  3,  '0',  NULL, '');
INSERT INTO "brm_auth_list" ("id", "userid", "brmid", "versionid", "permission", "approved", "comment", "timestamp") VALUES (3, 2,  1,  2,  3,  -1, NULL, 1423002341);
INSERT INTO "brm_auth_list" ("id", "userid", "brmid", "versionid", "permission", "approved", "comment", "timestamp") VALUES (4, 3,  1,  2,  3,  1,  NULL, 1423002341);
INSERT INTO "brm_auth_list" ("id", "userid", "brmid", "versionid", "permission", "approved", "comment", "timestamp") VALUES (5, 2,  1,  3,  3,  '0',  NULL, 1423002341);
INSERT INTO "brm_auth_list" ("id", "userid", "brmid", "versionid", "permission", "approved", "comment", "timestamp") VALUES (6, 1,  1,  3,  3,  1,  'This is good!',  1423774157);
INSERT INTO "brm_auth_list" ("id", "userid", "brmid", "versionid", "permission", "approved", "comment", "timestamp") VALUES (7, 2,  3,  5,  7,  '0',  NULL, NULL);
INSERT INTO "brm_auth_list" ("id", "userid", "brmid", "versionid", "permission", "approved", "comment", "timestamp") VALUES (8, 3,  3,  5,  7,  '0',  NULL, NULL);
INSERT INTO "brm_auth_list" ("id", "userid", "brmid", "versionid", "permission", "approved", "comment", "timestamp") VALUES (9, 1,  3,  5,  7,  '0',  NULL, NULL);

DROP TABLE IF EXISTS "brm_auth_view_list";
CREATE TABLE "brm_auth_view_list" (
  "timestamp" integer NOT NULL,
  "authid" integer NOT NULL,
  FOREIGN KEY ("authid") REFERENCES "brm_auth_list" ("id") ON DELETE CASCADE
);


DROP TABLE IF EXISTS "brm_campaigns";
CREATE TABLE "brm_campaigns" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "title" text NOT NULL,
  "description" text NOT NULL,
  "current_version" integer NULL,
  "templateid" text NOT NULL,
  "stateid" integer NOT NULL DEFAULT '0',
  "departmentid" integer NULL,
  "createdby" integer NOT NULL,
  "created" integer NOT NULL,
  FOREIGN KEY ("stateid") REFERENCES "brm_state" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION,
  FOREIGN KEY ("createdby") REFERENCES "user" ("id") ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY ("current_version") REFERENCES "brm_content_version" ("id") ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY ("departmentid") REFERENCES "departments" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION
);

DELETE FROM "brm_campaigns";
INSERT INTO "brm_campaigns" ("id", "title", "description", "current_version", "templateid", "stateid", "departmentid", "createdby", "created") VALUES (1, 'TEST BRM', 'Testing BRM',  3,  '', '0',  NULL, 1,  1422916676);
INSERT INTO "brm_campaigns" ("id", "title", "description", "current_version", "templateid", "stateid", "departmentid", "createdby", "created") VALUES (2, 'Moar Testing', 'This is to test the save functionality', 4,  '', '0',  NULL, 1,  1424279548);
INSERT INTO "brm_campaigns" ("id", "title", "description", "current_version", "templateid", "stateid", "departmentid", "createdby", "created") VALUES (3, 'Moar Testing', 'This is to test the save functionality', 5,  '', '0',  NULL, 1,  1424279565);

DROP TABLE IF EXISTS "brm_content_version";
CREATE TABLE "brm_content_version" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "brmid" integer NULL,
  "brmversionid" integer NULL,
  "content" text NOT NULL,
  "created" integer NOT NULL,
  FOREIGN KEY ("brmid") REFERENCES "brm_campaigns" ("id") ON DELETE CASCADE ON UPDATE NO ACTION
);

DELETE FROM "brm_content_version";
INSERT INTO "brm_content_version" ("id", "brmid", "brmversionid", "content", "created") VALUES (1,  1,  1,  'THIS IS TEST CONTENT!!!',  1422916676);
INSERT INTO "brm_content_version" ("id", "brmid", "brmversionid", "content", "created") VALUES (2,  1,  2,  'MOAR CONTENT HERE!!!!',  1423002341);
INSERT INTO "brm_content_version" ("id", "brmid", "brmversionid", "content", "created") VALUES (3,  1,  3,  'Another Version Here', 1422995305);
INSERT INTO "brm_content_version" ("id", "brmid", "brmversionid", "content", "created") VALUES (4,  2,  1,  'SAVING THIS CONTENT',  1424279548);
INSERT INTO "brm_content_version" ("id", "brmid", "brmversionid", "content", "created") VALUES (5,  3,  1,  'SAVING THIS CONTENT',  1424279565);

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


DROP TABLE IF EXISTS "brm_state";
CREATE TABLE "brm_state" (
  "id" integer NOT NULL,
  "name" text NOT NULL,
  "description" text NOT NULL,
  PRIMARY KEY ("id")
);

DELETE FROM "brm_state";
INSERT INTO "brm_state" ("id", "name", "description") VALUES ('0',  'Saved',  'BRM Email is Saved');
INSERT INTO "brm_state" ("id", "name", "description") VALUES (1,  'Sent For Approval',  'BRM Email was sent to auth list for approval');
INSERT INTO "brm_state" ("id", "name", "description") VALUES (2,  'Approved', 'BRM Email has met approval standards and is ready to insert in to BRM.');
INSERT INTO "brm_state" ("id", "name", "description") VALUES (3,  'Approved and Template Created',  'BRM Email Template was created and is waiting the sent date.');
INSERT INTO "brm_state" ("id", "name", "description") VALUES (4,  'Sent', 'BRM Email has been sent to the list.');
INSERT INTO "brm_state" ("id", "name", "description") VALUES (5,  'Ended',  'BRM Email Campaign has ended.');

DROP TABLE IF EXISTS "campaign";
CREATE TABLE "campaign" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" text NOT NULL,
  "description" text NOT NULL,
  "startdate" integer NOT NULL,
  "enddate" integer NOT NULL,
  "freezedate" integer NOT NULL,
  "created" integer NOT NULL
);


DROP TABLE IF EXISTS "comments";
CREATE TABLE "comments" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "userid" integer NOT NULL,
  "brmid" integer NOT NULL,
  "versionid" integer NOT NULL,
  "comment" text NOT NULL,
  "timestamp" integer NOT NULL,
  FOREIGN KEY ("userid") REFERENCES "user" ("id") ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY ("brmid") REFERENCES "brm_campaigns" ("id") ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY ("versionid") REFERENCES "brm_content_version" ("id") ON DELETE CASCADE ON UPDATE NO ACTION
);


DROP TABLE IF EXISTS "departments";
CREATE TABLE "departments" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" text NOT NULL
);

DELETE FROM "departments";
INSERT INTO "departments" ("id", "name") VALUES (1, 'IT (Information Technology)');
INSERT INTO "departments" ("id", "name") VALUES (2, 'Student Success');
INSERT INTO "departments" ("id", "name") VALUES (3, 'Recruitment');
INSERT INTO "departments" ("id", "name") VALUES (4, 'Advising');
INSERT INTO "departments" ("id", "name") VALUES (5, 'Housing');
INSERT INTO "departments" ("id", "name") VALUES (6, 'Registrar');
INSERT INTO "departments" ("id", "name") VALUES (7, 'Business Services');
INSERT INTO "departments" ("id", "name") VALUES (8, 'Scholarships');

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


DROP TABLE IF EXISTS "user";
CREATE TABLE "user" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "firstname" text NULL,
  "lastname" text NULL,
  "email" text NOT NULL,
  "permissions" integer NOT NULL DEFAULT '1'
);

DELETE FROM "user";
INSERT INTO "user" ("id", "firstname", "lastname", "email", "permissions") VALUES (1, '',  '',  '', 31);

DROP VIEW IF EXISTS "view_approved";
CREATE TABLE "view_approved" ("brmid" integer, "count" );


DROP VIEW IF EXISTS "view_auth_list";
CREATE TABLE "view_auth_list" ("id" integer, "title" text, "description" text, "current_version" integer, "templateid" text, "stateid" integer, "departmentid" integer, "createdby" integer, "created" integer, "version_count" , "version_created" , "auth_user" integer, "auth_permission" integer, "auth_approved" integer);


DROP VIEW IF EXISTS "view_brm_auth_list";
CREATE TABLE "view_brm_auth_list" ("id" integer, "userid" integer, "brmid" integer, "versionid" integer, "permission" integer, "approved" integer, "firstname" text, "lastname" text, "email" text, "user_permissions" integer, "view_count" , "lastviewed" );


DROP VIEW IF EXISTS "view_brm_comments";
CREATE TABLE "view_brm_comments" ("userid" integer, "brmid" integer, "versionid" integer, "brmversionid" integer, "comment" text, "timestamp" integer, "useremail" text, "userfirstname" text, "userlastname" text);


DROP VIEW IF EXISTS "view_brm_list";
CREATE TABLE "view_brm_list" ("id" integer, "title" text, "description" text, "current_version" integer, "templateid" text, "stateid" integer, "departmentid" integer, "createdby" integer, "created" integer, "version_count" , "version_created" );


DROP VIEW IF EXISTS "view_brm_list_approve";
CREATE TABLE "view_brm_list_approve" ("id" integer, "title" text, "description" text, "current_version" integer, "templateid" text, "stateid" integer, "departmentid" integer, "createdby" integer, "created" integer, "version_count" , "version_created" , "brm_current_version" integer, "approval_needed" , "approved" , "denied" );


DROP VIEW IF EXISTS "view_common_users";
CREATE TABLE "view_common_users" ("userid" integer, "firstname" text, "lastname" text, "email" text, "counter" );


DROP VIEW IF EXISTS "view_deny_approval";
CREATE TABLE "view_deny_approval" ("brmid" integer, "count" );


DROP VIEW IF EXISTS "view_need_approval";
CREATE TABLE "view_need_approval" ("brmid" integer, "count" );


DROP TABLE IF EXISTS "view_approved";
CREATE VIEW "view_approved" AS
SELECT "auth_list"."id" AS "brmid", COUNT("auth_list"."auth_approved") AS "count" FROM "view_auth_list" AS "auth_list" WHERE "auth_list"."auth_approved" = 1 GROUP BY "brmid";

DROP TABLE IF EXISTS "view_auth_list";
CREATE VIEW "view_auth_list" AS
SELECT "cbrm_list".*, "auth"."userid" AS "auth_user",
"auth"."permission" AS "auth_permission", "auth"."approved" AS "auth_approved" 
FROM "view_brm_list" AS "cbrm_list"
LEFT JOIN "brm_auth_list" AS "auth" ON "cbrm_list"."id" = "auth"."brmid" AND "cbrm_list"."current_version" = "auth"."versionid";

DROP TABLE IF EXISTS "view_brm_auth_list";
CREATE VIEW "view_brm_auth_list" AS
SELECT "brm_a"."id",
       "brm_a"."userid" AS "userid",
       "brm_a"."brmid" AS "brmid",
       "brm_a"."versionid" AS "versionid",
       "brm_a"."permission" AS "permission",
       "brm_a"."approved" AS "approved",
       "u"."firstname" AS "firstname",
       "u"."lastname" AS "lastname",
       "u"."email" AS "email",
       "u"."permissions" as "user_permissions",
       COUNT("brm_avl"."timestamp") AS "view_count",
       MAX("brm_avl"."timestamp") AS "lastviewed"
FROM "brm_auth_list" AS "brm_a"
LEFT JOIN "user" AS "u" ON "brm_a"."userid" = "u"."id"
LEFT JOIN "brm_auth_view_list" AS "brm_avl" ON "brm_avl"."authid" = "brm_a"."id"
GROUP BY "brm_a"."id";

DROP TABLE IF EXISTS "view_brm_comments";
CREATE VIEW "view_brm_comments" AS
SELECT "brm_auth"."userid" AS "userid", 
     "brm_auth"."brmid" AS "brmid", 
     "brm_auth"."versionid" AS "versionid",
     "brm_cv"."brmversionid" AS "brmversionid", 
     "brm_auth"."comment" AS "comment", 
     "brm_auth"."timestamp" AS "timestamp",
     "user"."email" AS "useremail",
     "user"."firstname" AS "userfirstname",
     "user"."lastname" AS "userlastname"
FROM "brm_auth_list" AS "brm_auth" 
LEFT JOIN "user" AS "user" ON "brm_auth"."userid" = "user"."id"
LEFT JOIN "brm_content_version" AS "brm_cv" ON "brm_auth"."versionid" = "brm_cv"."id"
WHERE "brm_auth"."comment" IS NOT NULL
UNION
SELECT "c"."userid" AS "userid", 
     "c"."brmid" AS "brmid", 
     "c"."versionid" AS "versionid",
     "brm_cv"."brmversionid" AS "brmversionid",
     "c"."comment" AS "comment", 
     "c"."timestamp" AS "timestamp",
    "user"."email" AS "useremail",
     "user"."firstname" AS "userfirstname",
     "user"."lastname" AS "userlastname"
FROM "comments" AS "c"
LEFT JOIN "user" AS "user" ON "c"."userid" = "user"."id"
LEFT JOIN "brm_content_version" AS "brm_cv" ON "c"."versionid" = "brm_cv"."id"
WHERE "c"."comment" IS NOT NULL;

DROP TABLE IF EXISTS "view_brm_list";
CREATE VIEW "view_brm_list" AS
SELECT "brm".*, COUNT("brm_versions"."id") AS "version_count", MAX("brm_versions"."created") AS "version_created" FROM "brm_campaigns" AS "brm"
LEFT JOIN "brm_content_version" AS "brm_versions" ON "brm"."id" = "brm_versions"."brmid" GROUP BY "brm"."id";

DROP TABLE IF EXISTS "view_brm_list_approve";
CREATE VIEW "view_brm_list_approve" AS
SELECT "brm_list". *,
"brm_cv"."brmversionid" AS "brm_current_version",
"approval_needed"."count" AS "approval_needed",
"approved"."count" AS "approved",
"denied"."count" AS "denied"
FROM "view_brm_list" AS "brm_list"
LEFT JOIN "view_need_approval" AS "approval_needed" ON "brm_list"."id" = "approval_needed"."brmid"
LEFT JOIN "view_approved" AS "approved" ON "brm_list"."id" = "approved"."brmid"
LEFT JOIN "view_deny_approval" AS "denied" ON "brm_list"."id" = "denied"."brmid"
LEFT JOIN "brm_content_version" AS "brm_cv" ON "brm_list"."current_version" = "brm_cv"."id";

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
SELECT "auth_list"."id" AS "brmid", COUNT("auth_list"."auth_approved") AS "count" FROM "view_auth_list" AS "auth_list" WHERE "auth_list"."auth_approved" = -1 GROUP BY "brmid";

DROP TABLE IF EXISTS "view_need_approval";
CREATE VIEW "view_need_approval" AS
SELECT "auth_list"."id" AS "brmid", COUNT("auth_list"."auth_approved") AS "count" FROM "view_auth_list" AS "auth_list" WHERE "auth_list"."auth_approved" = 0 GROUP BY "brmid";

-- 