export function formatDate(date, format = 'YYYY-MM-DD') {
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');

  if (format === 'YYYY-MM-DD') {
      return `${year}-${month}-${day}`;
  } else if (format === 'MM/DD/YYYY') {
      return `${month}/${day}/${year}`;
  }

  return d.toISOString();
}
