using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using Queue.Repositories;

namespace Queue.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class SubjectController : ControllerBase
    {
        private readonly GroupRepository _groupRepository;
        private readonly ScheduleRepository _scheduleRepository;
        private readonly ApplicationDbContext _context;

        public SubjectController(GroupRepository groupRepository, ScheduleRepository scheduleRepository, ApplicationDbContext context)
        {
            _groupRepository = groupRepository;
            _scheduleRepository = scheduleRepository;
            _context = context;
        }

        /// <summary>
        /// Загружает расписание для указанной группы и возвращает его.
        /// </summary>
        /// <param name="groupName">Название группы (например, "220501")</param>
        [HttpGet("schedule/{groupId}")]
        [Authorize]
        public async Task<IActionResult> GetScheduleForGroup(int groupId)
        {
            try
            {
                var group = await _context.Groups.FirstOrDefaultAsync(g => g.Id == groupId);
                if (group == null)
                    return NotFound($"Группа '{groupId}' не найдена.");

                var dates = await _context.Dates
                    .Where(d => d.GroupId == groupId)
                    .ToListAsync();

                return Ok(dates);
            }
            catch (Exception ex)
            {
                return StatusCode(500, $"Ошибка при загрузке расписания: {ex.Message}");
            }
        }


        [HttpGet("subjects/{groupId}")]
        [Authorize]
        public async Task<IActionResult> GetSubjectsByGroupId(int groupId)
        {
            try
            {
                var subjects = await _context.Subjects
                    .Where(s => s.GroupId == groupId)
                    .ToListAsync();

                if (!subjects.Any())
                    return NotFound("Предметы не найдены");

                return Ok(subjects);
            }
            catch (Exception ex)
            {
                return StatusCode(500, $"Ошибка при загрузке предметов: {ex.Message}");
            }
        }
    }
}
