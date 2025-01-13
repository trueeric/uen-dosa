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


// 班級表 連舊的來用就好
Table uen_classes {
  id integer [primary key]
  eclass varchar -- 班級代碼：例如 H101, J102
  grade integer --年級
  cclass varchar -- 班級名稱
  homeroom_teacher_id integer [ref: > uen_staff.id] --員工編號

  created_at timestamp
  updated_at timestamp
}

// 學生表 連舊的來用就好
Table students {
  id integer [primary key]
  user_id integer [ref: > users.id]
  class_id integer [ref: > classes.id]
  student_number varchar
  created_at timestamp
  updated_at timestamp
}

// 評分紀錄表
Table score_records {
  id integer [primary key]
  student_id integer [ref: > students.id]
  recorded_by integer [ref: > users.id]
  score_code varchar [ref: > score_items.score_code]
  description text
  date date
  status enum // pending, confirmed, cancelled
  created_at timestamp
  updated_at timestamp
}

// 附件檔案表
CREATE TABLE attachments (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '主鍵',
    attachable_type VARCHAR(191) NOT NULL COMMENT '關聯模型類型',
    attachable_id INT NOT NULL COMMENT '關聯模型ID',
    file_name VARCHAR(191) NOT NULL COMMENT '檔案名稱',
    file_path VARCHAR(191) NOT NULL COMMENT '檔案路徑',
    file_size INT NOT NULL COMMENT '檔案大小(bytes)',
    mime_type VARCHAR(100) NOT NULL COMMENT '檔案類型',
    remarks VARCHAR(191) NULL COMMENT '備註',
    sort TINYINT DEFAULT 0 COMMENT '排序',
    is_active BOOLEAN DEFAULT TRUE COMMENT '是否啟用',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',

    INDEX idx_attachable (attachable_type, attachable_id)
) COMMENT '附件檔案表';

// 評分項目表 score_items
Table  {
  id integer [primary key]
  score_code varchar
  score_type_code integer [ref: > score_types.code]
  score_item varchar
  description text
  points float
  is_active boolean
  created_at timestamp
  updated_at timestamp

  index idx_type_code (score_type_code,score_code)
}

// 評分類別表 score_types
Table  {
  id integer [primary key]
  score_type_code varchar
  score_type varchar
  memo varchar(190)
  points float
  sort tinyint
  is_active boolean
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
