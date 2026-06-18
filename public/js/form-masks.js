(() => {
  function digitsOnly(v) {
    return String(v ?? '').replace(/\D/g, '');
  }

  function maskCpfCnpj(el) {
    const d = digitsOnly(el.value);
    if (d.length <= 11) {
      el.value = d
        .replace(/^(\d{3})(\d)/, '$1.$2')
        .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/\.(\d{3})(\d)/, '.$1-$2')
        .replace(/(-\d{2})\d+?$/, '$1');
    } else {
      el.value = d
        .slice(0, 14)
        .replace(/^(\d{2})(\d)/, '$1.$2')
        .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/\.(\d{3})(\d)/, '.$1/$2')
        .replace(/(\d{4})(\d)/, '$1-$2');
    }
  }

  function maskPhone(el) {
    const d = digitsOnly(el.value).slice(0, 11);
    if (d.length <= 10) {
      el.value = d.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3').trim();
    } else {
      el.value = d.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3').trim();
    }
  }

  document.querySelectorAll('[data-mask="document"]').forEach((el) => {
    el.addEventListener('input', () => maskCpfCnpj(el));
  });
  document.querySelectorAll('[data-mask="phone"]').forEach((el) => {
    el.addEventListener('input', () => maskPhone(el));
  });
})();
