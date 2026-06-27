const term = "a*b";
try {
  const escaped = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  console.log('Escaped:', escaped);
  const regex = new RegExp('(^|\\s)' + escaped, 'i');
  console.log('Regex:', regex);
  console.log('Test:', regex.test("a*b"));
} catch (e) {
  console.error(e);
}
