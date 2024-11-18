const express = require('express');
const mysql = require('mysql2');
const cors = require('cors');

const app = express();
const port = 3001;

// CORS Ayarları
app.use(cors());
app.use(express.json());

const db = mysql.createPool({
  host: '127.0.0.1',
  port: 3306,
  user: 'root',
  password: '8520',
  database: 'survey_app',
});


app.post('/survey-data', (req, res) => {
  console.log(req.body.survey_id);
  const surveyId = req.body.survey_id;

  db.query(`
    SELECT s.id, s.title, s.description, s.share_link, 
      JSON_ARRAYAGG(
        JSON_OBJECT(
          'id', sq.id, 
          'question_text', sq.question_text,
          'section', sq.section,
          'type', sq.type,
          'options', (
            SELECT JSON_ARRAYAGG(
              JSON_OBJECT(
                'id', sqo.id, 
                'text', sqo.text,
                'type', sqo.type,
                'question_id', sqo.question_id
              )
            )
            FROM survey_question_options sqo
            WHERE sqo.question_id = sq.id
          )
        )
      ) AS questions
    FROM surveys s
    LEFT JOIN survey_questions sq ON s.id = sq.survey_id
    WHERE s.share_link = ?
    GROUP BY s.id`, 
    [surveyId], 
    (err, results) => {
      if (err) {
        console.error('Database Error:', err);
        return res.status(500).json({ success: false, error: 'Database error', err: err });
      }
      res.status(200).json({ success: true, data: results });
    });
});


app.listen(port, () => {
  console.log(`Express server running on http://localhost:${port}`);
});
