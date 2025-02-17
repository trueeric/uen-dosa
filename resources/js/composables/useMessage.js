export function useMessage() {
  const showMessage = (message, type = 'info') => {
      console.log(`[${type.toUpperCase()}]: ${message}`);
  };

  return { showMessage };
}
