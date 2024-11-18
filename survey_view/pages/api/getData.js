import db from '../../server';

export default async function handler(req, res) {
  if (req.method === 'GET') {
    try {
      const [rows] = await db.query('SELECT * FROM tablo_adi');
      res.status(200).json({ success: true, data: rows });
    } catch (error) {
      console.error('Database Error:', error);
      res.status(500).json({ success: false, error: 'Database error' });
    }
  } else {
    res.status(405).json({ success: false, error: 'Method not allowed' });
  }
}
