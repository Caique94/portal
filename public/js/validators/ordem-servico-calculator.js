/**
 * OrdemServiço Calculator - Frontend validation and calculation utilities
 * PT-BR formatting support for currency and time calculations
 */

class OrdemServicoCalculator {
  /**
   * Parse HH:MM string to minutes
   * @param {string} timeStr - Time in HH:MM format or empty
   * @returns {number} Minutes
   */
  static timeToMinutes(timeStr) {
    if (!timeStr || timeStr === '--:--' || timeStr === '') {
      return 0;
    }

    if (timeStr.indexOf(':') === -1) {
      return parseInt(timeStr);
    }

    const [horas, minutos] = timeStr.trim().split(':');
    return parseInt(horas) * 60 + parseInt(minutos);
  }

  /**
   * Format minutes to HH:MM string
   * @param {number} minutos - Minutes
   * @returns {string} HH:MM format
   */
  static minutesToTime(minutos) {
    const horas = Math.floor(minutos / 60);
    const mins = minutos % 60;
    return `${String(horas).padStart(2, '0')}:${String(mins).padStart(2, '0')}`;
  }

  /**
   * Convert minutes to decimal hours with 2 decimal places
   * @param {number} minutos - Minutes
   * @returns {number} Decimal hours (e.g., 9.50)
   */
  static minutesToDecimalHours(minutos) {
    return Math.round((minutos / 60) * 100) / 100;
  }

  /**
   * Calculate total hours: (hora_fim - hora_inicio - hora_desconto)
   * @param {string} horaInicio - Start time (HH:MM)
   * @param {string} horaFim - End time (HH:MM)
   * @param {string} horaDesconto - Discount time (HH:MM), optional
   * @returns {number} Total hours in decimal format (e.g., 9.00, 7.50)
   */
  static calculateTotalHoras(horaInicio, horaFim, horaDesconto = '') {
    const minutosInicio = this.timeToMinutes(horaInicio);
    const minutosFim = this.timeToMinutes(horaFim);
    const minutosDesconto = this.timeToMinutes(horaDesconto);

    // Calculate duration: fim - inicio
    let duracaoMinutos = minutosFim - minutosInicio;

    // Subtract discount if exists
    if (minutosDesconto > 0) {
      duracaoMinutos -= minutosDesconto;
    }

    // If result is negative, return 0.00
    if (duracaoMinutos < 0) {
      return 0.00;
    }

    return this.minutesToDecimalHours(duracaoMinutos);
  }

  /**
   * Format currency to PT-BR (R$ 435,00)
   * @param {number|string} value - Value to format
   * @returns {string} Formatted currency string
   */
  static formatCurrency(value) {
    if (value === null || value === undefined || value === '') {
      return 'R$ 0,00';
    }

    const numValue = parseFloat(value);
    if (isNaN(numValue)) {
      return 'R$ 0,00';
    }

    return 'R$ ' + numValue.toLocaleString('pt-BR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  /**
   * Format decimal hours (9.00, 7.50)
   * @param {number} hours - Decimal hours
   * @returns {string} Formatted hours
   */
  static formatHours(hours) {
    const numHours = parseFloat(hours);
    if (isNaN(numHours)) {
      return '0.00';
    }
    return numHours.toFixed(2);
  }

  /**
   * Apply time defaults to form fields
   * If hora_desconto is empty, display as 00:00
   * @param {Object} data - Form data object
   * @returns {Object} Data with defaults applied
   */
  static applyDefaults(data) {
    const result = { ...data };

    // Apply 00:00 defaults for empty time fields
    if (!result.hora_inicio || result.hora_inicio === '--:--') {
      result.hora_inicio = '00:00';
    }
    if (!result.hora_final || result.hora_final === '--:--') {
      result.hora_final = '00:00';
    }
    if (!result.hora_desconto || result.hora_desconto === '--:--') {
      result.hora_desconto = '00:00';
    }

    return result;
  }

  /**
   * Run automated test cases and return validation results
   * Test case 1: 08:00→17:00, no discount = 9.00 hours
   * Test case 2: 08:00→17:00, 01:30 discount = 7.50 hours
   * Test case 3: Empty times = 0.00 hours
   * @returns {Object} Validation results with pass/fail status
   */
  static runValidationTests() {
    const results = {};

    // Test 1: No discount
    const test1 = this.calculateTotalHoras('08:00', '17:00', '');
    results.test1_no_discount = {
      input: { horaInicio: '08:00', horaFim: '17:00', horaDesconto: '' },
      expected: 9.00,
      actual: test1,
      pass: Math.abs(test1 - 9.00) < 0.01,
      message: `Test 1 (08:00→17:00, no discount): ${Math.abs(test1 - 9.00) < 0.01 ? 'PASS' : 'FAIL'} (expected 9.00, got ${test1})`
    };

    // Test 2: With 01:30 discount
    const test2 = this.calculateTotalHoras('08:00', '17:00', '01:30');
    results.test2_with_discount = {
      input: { horaInicio: '08:00', horaFim: '17:00', horaDesconto: '01:30' },
      expected: 7.50,
      actual: test2,
      pass: Math.abs(test2 - 7.50) < 0.01,
      message: `Test 2 (08:00→17:00, 01:30 discount): ${Math.abs(test2 - 7.50) < 0.01 ? 'PASS' : 'FAIL'} (expected 7.50, got ${test2})`
    };

    // Test 3: Empty times
    const test3 = this.calculateTotalHoras('', '', '');
    results.test3_empty_times = {
      input: { horaInicio: '', horaFim: '', horaDesconto: '' },
      expected: 0.00,
      actual: test3,
      pass: test3 === 0.00,
      message: `Test 3 (empty times): ${test3 === 0.00 ? 'PASS' : 'FAIL'} (expected 0.00, got ${test3})`
    };

    // Summarize
    const passCount = Object.values(results)
      .filter(t => t.pass !== undefined)
      .reduce((sum, t) => sum + (t.pass ? 1 : 0), 0);

    results.summary = {
      total_tests: 3,
      passed: passCount,
      failed: 3 - passCount,
      all_passed: passCount === 3
    };

    return results;
  }

  /**
   * Validate visual output against expected values
   * @param {Object} actualValues - Actual calculated values
   * @param {Object} expectedValues - Expected values
   * @returns {Object} Validation report
   */
  static validateVisual(actualValues, expectedValues) {
    return {
      totalHoras: `${this.formatHours(actualValues.totalHoras)} === expected ${this.formatHours(expectedValues.totalHoras)}`,
      totalGeral: `${this.formatCurrency(actualValues.totalGeral)} === expected ${this.formatCurrency(expectedValues.totalGeral)}`,
      passes: Math.abs(parseFloat(actualValues.totalHoras) - parseFloat(expectedValues.totalHoras)) < 0.01 &&
              Math.abs(parseFloat(actualValues.totalGeral) - parseFloat(expectedValues.totalGeral)) < 0.01
    };
  }
}

/**
 * AUTO-RUN TESTS ON PAGE LOAD (for development/validation)
 * Comment out the next line in production
 */
if (typeof document !== 'undefined' && document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function() {
    console.log('OrdemServicoCalculator Tests:', OrdemServicoCalculator.runValidationTests());
  });
} else if (typeof document !== 'undefined') {
  console.log('OrdemServicoCalculator Tests:', OrdemServicoCalculator.runValidationTests());
}
