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

-- 學期主表：記錄學校各學期的基本資訊和狀態
CREATE TABLE uen_semesters (
    id BIGINT PRIMARY KEY AUTO_INCREMENT COMMENT '主鍵ID',
    school_year VARCHAR(10) NOT NULL COMMENT '學年度（例如：110）',
    semester_no VARCHAR(2) NOT NULL COMMENT '學期別（1:第一學期, 2:第二學期, 10:暑季班, 20:寒季班）',
    semester VARCHAR(10) NOT NULL COMMENT '學期代碼（格式：學年度-學期，例如：110-1）',
    start_date DATE NOT NULL COMMENT '學期開始日期',
    end_date DATE NOT NULL COMMENT '學期結束日期',
    first_monday DATE NOT NULL COMMENT '學期第一週的星期一日期（用於計算週次）',
    week_count UNSIGNED TINYINT NOT NULL COMMENT '該學期總週數',
    is_active BOOLEAN DEFAULT true COMMENT '是否為當前學期（true:是, false:否）',
    status ENUM('preparing', 'active', 'closed') DEFAULT 'preparing' COMMENT '學期狀態（preparing:籌備中, active:進行中, closed:已結束）',
    created_at TIMESTAMP COMMENT '建立時間',
    updated_at TIMESTAMP COMMENT '更新時間',

    UNIQUE KEY `unique_semester` (semester) COMMENT '學期代碼唯一索引',
    UNIQUE KEY `unique_school_year_semester` (school_year, semester_no) COMMENT '學年度-學期別組合唯一索引',
    CHECK (start_date <= end_date),
    CHECK (first_monday <= start_date AND first_monday <= end_date)
) COMMENT '學期主表：記錄學校各學期基本資訊';

-- 學期週次表：記錄每個學期的週次詳細資訊
CREATE TABLE uen_semester_weeks (
    id BIGINT PRIMARY KEY AUTO_INCREMENT COMMENT '主鍵ID',
    semester_id BIGINT NOT NULL COMMENT '關聯學期ID',
    week_no INT NOT NULL COMMENT '週次（從1開始計算）',
    start_date DATE NOT NULL COMMENT '該週開始日期（週一）',
    end_date DATE NOT NULL COMMENT '該週結束日期（週日）',
    is_holiday BOOLEAN DEFAULT false COMMENT '是否為假期週（true:是, false:否）',
    note VARCHAR(100) COMMENT '週次備註（例如：期中考週、校慶週等）',
    created_at TIMESTAMP COMMENT '建立時間',
    updated_at TIMESTAMP COMMENT '更新時間',

    FOREIGN KEY (semester_id) REFERENCES uen_semesters(id),
    UNIQUE KEY `unique_semester_week` (semester_id, week_no) COMMENT '學期-週次組合唯一索引'
) COMMENT '學期週次表：記錄每個學期的週次詳細資訊';


-- 基礎班級代碼表：儲存班級的基本資訊，不隨學期變動的固定資料
CREATE TABLE uen_classes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT COMMENT '主鍵ID',
    class_no VARCHAR(10) UNIQUE NOT NULL COMMENT '班級代碼（如：H101、H102）',
    grade VARCHAR(10) COMMENT '年級',
    class_name VARCHAR(50) COMMENT '班級名稱（如：一年一班、二年二班）',
    created_at TIMESTAMP COMMENT '建立時間',
    updated_at TIMESTAMP COMMENT '更新時間'
) COMMENT '基礎班級代碼表：記錄學校班級的基本資訊';


-- 學期班級表：記錄每個班級在不同學期的狀態和配置
CREATE TABLE uen_semester_classes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT COMMENT '主鍵ID',
    class_id BIGINT NOT NULL COMMENT '關聯基礎班級ID',
    semester VARCHAR(10) NOT NULL COMMENT '學期代碼（格式：學年度-學期，如：112-1）',
    homeroom_teacher_id BIGINT COMMENT '該學期班級導師ID',
    class_count UNSIGNED TINYINT NOT NULL default=0 COMMENT '該學期班級人數',
    created_at TIMESTAMP COMMENT '建立時間',
    updated_at TIMESTAMP COMMENT '更新時間',

    FOREIGN KEY (class_id) REFERENCES uen_classes(id) COMMENT '外鍵：關聯到基礎班級表',
    FOREIGN KEY (homeroom_teacher_id) REFERENCES uen_staff(id) COMMENT '外鍵：關聯到教職員工表',
    UNIQUE KEY `unique_class_semester` (class_id, semester) COMMENT '班級-學期組合唯一索引'
) COMMENT '學期班級表：記錄每個班級在各學期的狀態資訊';

-- 學生學期班級關聯表：記錄學生在每個學期的班級歸屬
CREATE TABLE student_semester_classes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT COMMENT '主鍵ID',
    student_id BIGINT NOT NULL COMMENT '學生ID',
    semester_class_id BIGINT NOT NULL COMMENT '學期班級ID',
    semester VARCHAR(10) NOT NULL COMMENT '學期代碼（格式：學年度-學期，如：112-1）',
    created_at TIMESTAMP COMMENT '建立時間',
    updated_at TIMESTAMP COMMENT '更新時間',

    FOREIGN KEY (student_id) REFERENCES uen_students(id) COMMENT '外鍵：關聯到學生基本資料表',
    FOREIGN KEY (semester_class_id) REFERENCES semester_classes(id) COMMENT '外鍵：關聯到學期班級表',
    UNIQUE KEY `unique_student_semester` (student_id, semester) COMMENT '學生-學期組合唯一索引'
) COMMENT '學生學期班級關聯表：記錄學生在各學期的班級歸屬資訊';

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
