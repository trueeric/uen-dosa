// 使用者表
Table users {
  id integer [primary key]
  name varchar
  email varchar
  password varchar
  created_at timestamp
  updated_at timestamp
}

// 科目表
Table uen_courses {
  id integer [primary key]
  eclass varchar -- 班級代碼：例如 H101, J102
  grade integer --年級
  cclass varchar -- 班級名稱
  homeroom_teacher_id integer [ref: > uen_staff.id] --員工編號

  created_at timestamp
  updated_at timestamp
}


// 班級表
Table uen_classes {
  id integer [primary key]
  eclass varchar -- 班級代碼：例如 H101, J102
  grade integer --年級
  cclass varchar -- 班級名稱
  homeroom_teacher_id integer [ref: > uen_staff.id] --員工編號

  created_at timestamp
  updated_at timestamp
}

// 學生表
Table students {
  id integer [primary key]
  user_id integer [ref: > users.id]
  class_id integer [ref: > classes.id]
  student_number varchar
  created_at timestamp
  updated_at timestamp
}

// 違規紀錄表
Table violations {
  id integer [primary key]
  student_id integer [ref: > students.id]
  recorded_by integer [ref: > users.id]
  violation_type_id integer [ref: > violation_types.id]
  description text
  date date
  status enum // pending, confirmed, cancelled
  created_at timestamp
  updated_at timestamp
}

// 違規類型表
Table violation_types {
  id integer [primary key]
  name varchar
  description text
  points integer
  created_at timestamp
  updated_at timestamp
}

// 運動會項目表
Table sports_events {
  id integer [primary key]
  name varchar
  event_type enum // track, field, team
  max_participants integer
  grade_restriction json
  gender_restriction enum // male, female, all
  event_date datetime
  created_at timestamp
  updated_at timestamp
}

// 報名表
Table registrations {
  id integer [primary key]
  student_id integer [ref: > students.id]
  event_id integer [ref: > sports_events.id]
  class_id integer [ref: > classes.id]
  status enum // pending, approved, cancelled
  created_at timestamp
  updated_at timestamp
}
