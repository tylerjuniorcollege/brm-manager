DROP TABLE IF EXISTS "brm_auth_list";
CREATE TABLE "brm_auth_list" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "userid" integer NOT NULL,
  "brmid" integer NOT NULL,
  "versionid" integer NOT NULL,
  "permission" integer NOT NULL,
  "approved" integer NOT NULL DEFAULT '0',
  "comment" text NULL,
  "timestamp" integer NULL, "viewedtime" integer NULL,
  FOREIGN KEY ("userid") REFERENCES "user" ("id") ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY ("brmid") REFERENCES "brm_campaigns" ("id") ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY ("versionid") REFERENCES "brm_content_version" ("id") ON DELETE CASCADE ON UPDATE NO ACTION
);


DROP TABLE IF EXISTS "brm_campaigns";
CREATE TABLE "brm_campaigns" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "title" text NOT NULL,
  "description" text NULL,
  "current_version" integer NULL,
  "templateid" text NULL,
  "campaignid" integer NULL,
  "stateid" integer NOT NULL DEFAULT '0',
  "requestid" integer NULL,
  "launchdate" integer NULL,
  "population" integer NULL,
  "listname" text NULL,
  "createdby" integer NOT NULL,
  "created" integer NOT NULL,
  FOREIGN KEY ("current_version") REFERENCES "brm_content_version" ("id") ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY ("createdby") REFERENCES "user" ("id") ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY ("stateid") REFERENCES "brm_state" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION,
  FOREIGN KEY ("campaignid") REFERENCES "campaign" ("id") ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY ("requestid") REFERENCES "brm_requests" ("id") ON DELETE CASCADE ON UPDATE NO ACTION
);


DROP TABLE IF EXISTS "brm_content_version";
CREATE TABLE "brm_content_version" (
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


DELIMITER ;;
CREATE TRIGGER "brm_content_version_ai" AFTER    INSERT ON "brm_content_version"
BEGIN
  UPDATE "brm_content_version" SET "brmversionid" = (SELECT COUNT(id) FROM "brm_content_version" WHERE "brmid" = NEW.brmid) WHERE "id" = NEW.id;
END;;

DELIMITER ;

DROP TABLE IF EXISTS "brm_header_images";
CREATE TABLE "brm_header_images" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "brmid" integer NULL,
  "brmversionid" integer NULL,
  "filename" text NOT NULL,
  "created" integer NOT NULL,
  "uploadedby" integer NOT NULL,
  FOREIGN KEY ("uploadedby") REFERENCES "user" ("id") ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY ("brmversionid") REFERENCES "brm_content_version" ("id") ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY ("brmid") REFERENCES "brm_campaigns" ("id") ON DELETE CASCADE ON UPDATE NO ACTION
);


DROP TABLE IF EXISTS "brm_requests";
CREATE TABLE "brm_requests" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "userid" integer NULL,
  "timestamp" integer NULL,
  "departmentid" integer NULL,
  "email" text NULL,
  FOREIGN KEY ("userid") REFERENCES "user" ("id") ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY ("departmentid") REFERENCES "departments" ("id") ON DELETE CASCADE ON UPDATE NO ACTION
);


DROP TABLE IF EXISTS "brm_state";
CREATE TABLE "brm_state" (
  "id" integer NOT NULL,
  "name" text NOT NULL,
  "description" text NOT NULL,
  PRIMARY KEY ("id")
);

INSERT INTO "brm_state" ("id", "name", "description") VALUES ('0',  'Saved',  'BRM Email is Saved');
INSERT INTO "brm_state" ("id", "name", "description") VALUES (1,  'Sent For Approval',  'BRM Email was sent to auth list for approval');
INSERT INTO "brm_state" ("id", "name", "description") VALUES (2,  'Approved', 'BRM Email has met approval standards and is ready to insert in to BRM.');
INSERT INTO "brm_state" ("id", "name", "description") VALUES (3,  'Approved and Template Created',  'BRM Email Template was created and is waiting the sent date.');
INSERT INTO "brm_state" ("id", "name", "description") VALUES (4,  'Sent', 'BRM Email has been sent to the list.');
INSERT INTO "brm_state" ("id", "name", "description") VALUES (5,  'Ended',  'BRM Email Campaign has ended.');
INSERT INTO "brm_state" ("id", "name", "description") VALUES (6,  'Denied', 'This BRM has been Denied');

DROP TABLE IF EXISTS "brm_state_change";
CREATE TABLE "brm_state_change" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "brmid" integer NOT NULL,
  "versionid" integer NOT NULL,
  "userid" integer NOT NULL,
  "stateid" integer NOT NULL,
  "timestamp" integer NOT NULL,
  FOREIGN KEY ("brmid") REFERENCES "brm_campaigns" ("id") ON DELETE CASCADE,
  FOREIGN KEY ("versionid") REFERENCES "brm_content_version" ("id") ON DELETE CASCADE,
  FOREIGN KEY ("userid") REFERENCES "user" ("id") ON DELETE CASCADE,
  FOREIGN KEY ("stateid") REFERENCES "brm_state" ("id") ON DELETE CASCADE
);


DROP TABLE IF EXISTS "campaign";
CREATE TABLE "campaign" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" text NOT NULL,
  "description" text NOT NULL,
  "startdate" integer NULL,
  "enddate" integer NULL,
  "freezedate" integer NULL,
  "created" integer NOT NULL,
  "createdby" integer NOT NULL,
  FOREIGN KEY ("createdby") REFERENCES "user" ("id") ON DELETE CASCADE
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
  "authid" integer NULL,
  "emailid" text NULL,
  "result" integer NOT NULL DEFAULT '0',
  FOREIGN KEY ("userid") REFERENCES "user" ("id") ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY ("authid") REFERENCES "brm_auth_list" ("id") ON DELETE CASCADE ON UPDATE NO ACTION
);


DROP TABLE IF EXISTS "user";
CREATE TABLE "user" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "firstname" text NULL,
  "lastname" text NULL,
  "email" text NOT NULL,
  "permissions" integer NOT NULL DEFAULT '1',
  "created" integer NOT NULL
);


DROP VIEW IF EXISTS "view_approved";
CREATE TABLE "view_approved" ("brmid" integer, "count" );


DROP VIEW IF EXISTS "view_audit_log";
CREATE TABLE "view_audit_log" ("timestamp" integer, "brmid" , "versionid" , "userid" integer, "action" , "message" );


DROP VIEW IF EXISTS "view_auth_list";
CREATE TABLE "view_auth_list" ("id" integer, "title" text, "description" text, "current_version" integer, "templateid" text, "campaignid" integer, "stateid" integer, "requestid" integer, "launchdate" integer, "population" integer, "listname" text, "createdby" integer, "created" integer, "auth_user" integer, "auth_permission" integer, "auth_approved" integer);


DROP VIEW IF EXISTS "view_brm_comments";
CREATE TABLE "view_brm_comments" ("userid" integer, "brmid" integer, "versionid" integer, "brmversionid" integer, "comment" text, "timestamp" integer, "useremail" text, "userfirstname" text, "userlastname" text);


DROP VIEW IF EXISTS "view_brm_list";
CREATE TABLE "view_brm_list" ("id" integer, "title" text, "description" text, "current_version" integer, "brm_current_version" integer, "templateid" text, "stateid" integer, "state" text, "requestid" integer, "request_userid" integer, "request_user_email" text, "request_timestamp" integer, "request_departmentid" integer, "request_department_name" text, "request_email" text, "launchdate" integer, "population" integer, "listname" text, "createdby" integer, "created" integer, "approval_needed" , "approved" , "denied" );


DROP VIEW IF EXISTS "view_common_users";
CREATE TABLE "view_common_users" ("userid" integer, "firstname" text, "lastname" text, "email" text, "counter" );


DROP VIEW IF EXISTS "view_deny_approval";
CREATE TABLE "view_deny_approval" ("brmid" integer, "count" );


DROP VIEW IF EXISTS "view_need_approval";
CREATE TABLE "view_need_approval" ("brmid" integer, "count" );


DROP TABLE IF EXISTS "view_approved";
CREATE VIEW "view_approved" AS
SELECT "auth_list"."id" AS "brmid", COUNT("auth_list"."auth_approved") AS "count" FROM "view_auth_list" AS "auth_list" WHERE "auth_list"."auth_approved" = 1 GROUP BY "brmid";

DROP TABLE IF EXISTS "view_audit_log";
CREATE VIEW "view_audit_log" AS
SELECT
  "u"."created" AS "timestamp",
  NULL AS "brmid",
  NULL AS "versionid",
  "u"."id" AS "userid",
  'user_created' AS "action",
  'User Created: ID#' || "u"."id" || ": " || "u"."email" AS "message"
FROM
  "user" AS "u"
UNION
SELECT
  "brm"."created" AS "timestamp",
  "brm"."id" AS "brmid",
  "brm_ver"."id" AS "versionid",
  "brm"."createdby" AS "userid",
  'brm_created' AS "action",
  'BRM Created: ID#' || "brm"."id" || ' - ' || "brm"."title" AS "message"
FROM
  "brm_campaigns" AS "brm"
LEFT JOIN "brm_content_version" AS "brm_ver" ON "brm"."id" = "brm_ver"."brmid" AND "brm_ver"."brmversionid" = 1
UNION
SELECT
  "brm_cv"."created" AS "timestamp",
  "brm_cv"."brmid" AS "brmid",
  "brm_cv"."id" AS "versionid",
  "brm_cv"."userid" AS "userid",
  'version_created' AS "action",
  'Version Created: ID#' || "brm_cv"."id" || ' - LID#' || "brm_cv"."brmversionid" AS "message"
FROM
  "brm_content_version" AS "brm_cv"
WHERE
  "brm_cv"."brmversionid" > 1
UNION
SELECT
  "c"."timestamp" AS "timestamp",
  "c"."brmid" AS "brmid",
  "c"."versionid" AS "versionid",
  "c"."userid" AS "userid",
  'comment' AS "action",
  'Comment Added: ID#' || "c"."id" || ' - ' || "c"."comment" AS "message"
FROM
  "comments" AS "c"
UNION
SELECT
  "brm_al"."timestamp" AS "timestamp",
  "brm_al"."brmid" AS "brmid",
  "brm_al"."versionid" AS "versionid",
  "brm_al"."userid" AS "userid",
  'brm_approved' AS "action",
  'BRM Approved By ' || "u"."firstname" || ' ' || "u"."lastname" || ' - ID#' || "brm_al"."id" || ': ' || ifnull("brm_al"."comment", '') AS "message"
FROM
  "brm_auth_list" AS "brm_al"
LEFT JOIN "user" AS "u" ON "brm_al"."userid" = "u"."id"
WHERE "approved" = 1
UNION
SELECT
  "brm_al"."timestamp" AS "timestamp",
  "brm_al"."brmid" AS "brmid",
  "brm_al"."versionid" AS "versionid",
  "brm_al"."userid" AS "userid",
  'brm_denied' AS "action",
  'BRM Denied By ' || "u"."firstname" || ' ' || "u"."lastname" || ' - ID#' || "brm_al"."id" || ': ' || ifnull("brm_al"."comment", '') AS "message"
FROM
  "brm_auth_list" AS "brm_al"
LEFT JOIN "user" AS "u" ON "brm_al"."userid" = "u"."id"
WHERE "approved" = -1;

DROP TABLE IF EXISTS "view_auth_list";
CREATE VIEW "view_auth_list" AS
SELECT "cbrm_list".*, "auth"."userid" AS "auth_user",
"auth"."permission" AS "auth_permission", "auth"."approved" AS "auth_approved" 
FROM "brm_campaigns" AS "cbrm_list"
LEFT JOIN "brm_auth_list" AS "auth" ON "cbrm_list"."id" = "auth"."brmid" AND "cbrm_list"."current_version" = "auth"."versionid";

DROP TABLE IF EXISTS "view_brm_comments";
CREATE VIEW "view_brm_comments" AS
SELECT "brm_auth"."userid" AS "userid", 
     "brm_auth"."brmid" AS "brmid", 
     "brm_auth"."versionid" AS "versionid",
     "brm_cv"."brmversionid" AS "brmversionid",
     "brm_auth"."approved" AS "approved", 
     "brm_auth"."comment" AS "comment", 
     "brm_auth"."timestamp" AS "timestamp",
     "user"."email" AS "useremail",
     "user"."firstname" AS "userfirstname",
     "user"."lastname" AS "userlastname"
FROM "brm_auth_list" AS "brm_auth" 
LEFT JOIN "user" AS "user" ON "brm_auth"."userid" = "user"."id"
LEFT JOIN "brm_content_version" AS "brm_cv" ON "brm_auth"."versionid" = "brm_cv"."id"
WHERE "brm_auth"."comment" IS NOT NULL AND "brm_auth"."comment" != ''
UNION
SELECT "c"."userid" AS "userid", 
     "c"."brmid" AS "brmid", 
     "c"."versionid" AS "versionid",
     "brm_cv"."brmversionid" AS "brmversionid",
     0 AS "approved",
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
SELECT "brm_list"."id" AS "id",
       "brm_list"."title" AS "title",
       "brm_list"."description" AS "description",
       "brm_list"."current_version" AS "current_version",
       "brm_cv"."brmversionid" AS "brm_current_version",
       "brm_list"."templateid" AS "templateid",
       "brm_list"."stateid" AS "stateid",
       "state"."name" AS "state",
       "brm_list"."requestid" AS "requestid",
       "request"."userid" AS "request_userid",
       "request_user"."email" AS "request_user_email",
       strftime("%m-%d-%Y %H:%M:%S", "request"."timestamp", "unixepoch") AS "request_timestamp",
       "request"."departmentid" AS "request_departmentid",
       "request_department"."name" AS "request_department_name",
       "request"."email" AS "request_email",
       strftime("%m-%d-%Y %H:%M:%S", "brm_list"."launchdate", "unixepoch") AS "launchdate",
       "brm_list"."population" AS "population",
       "brm_list"."listname" AS "listname",
       "brm_list"."createdby" AS "createdby",
       "creator_user"."email" AS "createdby_email",
       "creator_user"."firstname" || ' ' || "creator_user"."lastname" AS "createdby_name",
       strftime("%m-%d-%Y %H:%M:%S", "brm_list"."created", "unixepoch") AS "created",
       "approval_needed"."count" AS "approval_needed",
       "approved"."count" AS "approved",
       "denied"."count" AS "denied"
       FROM "brm_campaigns" AS "brm_list"
       LEFT JOIN "view_need_approval" AS "approval_needed" ON "brm_list"."id" = "approval_needed"."brmid"
       LEFT JOIN "view_approved" AS "approved" ON "brm_list"."id" = "approved"."brmid"
       LEFT JOIN "view_deny_approval" AS "denied" ON "brm_list"."id" = "denied"."brmid"
       LEFT JOIN "brm_content_version" AS "brm_cv" ON "brm_list"."current_version" = "brm_cv"."id"
       LEFT JOIN "brm_state" AS "state" ON "brm_list"."stateid" = "state"."id"
       LEFT JOIN "brm_requests" AS "request" ON "brm_list"."requestid" = "request"."id"
       LEFT JOIN "user" AS "creator_user" ON "brm_list"."createdby" = "creator_user"."id"
       LEFT JOIN "user" AS "request_user" ON "request"."userid" = "request_user"."id"
       LEFT JOIN "departments" AS "request_department" ON "request"."departmentid" = "request_department"."id";

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