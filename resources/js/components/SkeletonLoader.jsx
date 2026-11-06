import React from 'react';

export function TaxCheckSkeleton() {
  return (
    <div className="space-y-4 animate-pulse">
      {/* Vehicle Info Card Skeleton */}
      <div className="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3">
        <div className="flex justify-between items-center border-b border-slate-100 pb-3">
          <div className="space-y-1.5 w-1/2">
            <div className="h-5 bg-slate-200 rounded-lg w-32"></div>
            <div className="h-3 bg-slate-100 rounded w-44"></div>
          </div>
          <div className="h-6 bg-slate-200 rounded-lg w-20"></div>
        </div>

        <div className="grid grid-cols-2 gap-3 pt-1">
          <div className="space-y-1">
            <div className="h-2.5 bg-slate-100 rounded w-16"></div>
            <div className="h-4 bg-slate-200 rounded w-28"></div>
          </div>
          <div className="space-y-1">
            <div className="h-2.5 bg-slate-100 rounded w-20"></div>
            <div className="h-4 bg-emerald-100 rounded w-24"></div>
          </div>
          <div className="space-y-1">
            <div className="h-2.5 bg-slate-100 rounded w-16"></div>
            <div className="h-4 bg-slate-200 rounded w-24"></div>
          </div>
          <div className="space-y-1">
            <div className="h-2.5 bg-slate-100 rounded w-20"></div>
            <div className="h-4 bg-slate-200 rounded w-24"></div>
          </div>
          <div className="space-y-1">
            <div className="h-2.5 bg-slate-100 rounded w-14"></div>
            <div className="h-4 bg-slate-200 rounded w-28"></div>
          </div>
          <div className="space-y-1">
            <div className="h-2.5 bg-slate-100 rounded w-24"></div>
            <div className="h-4 bg-slate-200 rounded w-28"></div>
          </div>
        </div>
      </div>

      {/* Bill Breakdown Skeleton */}
      <div className="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3">
        <div className="h-3 bg-slate-200 rounded w-36 mb-2"></div>

        {[1, 2, 3, 4].map((i) => (
          <div key={i} className="flex justify-between items-center py-1">
            <div className="h-3.5 bg-slate-100 rounded w-36"></div>
            <div className="h-4 bg-slate-200 rounded w-24"></div>
          </div>
        ))}

        <div className="border-t border-slate-100 pt-3 mt-2 flex justify-between items-center">
          <div className="space-y-1">
            <div className="h-3.5 bg-slate-200 rounded w-28"></div>
            <div className="h-2.5 bg-slate-100 rounded w-20"></div>
          </div>
          <div className="h-7 bg-slate-300 rounded-lg w-36"></div>
        </div>
      </div>
    </div>
  );
}

export function SimulateSkeleton() {
  return (
    <div className="space-y-4 animate-pulse">
      {/* Savings Banner Skeleton */}
      <div 
        className="h-24 card-gradient-emerald-action rounded-2xl p-4 flex justify-between items-center"
        style={{
          backgroundColor: '#059669',
          background: 'linear-gradient(90deg, #059669 0%, #0f766e 100%)',
        }}
      >
        <div className="space-y-2 w-1/2">
          <div className="h-3 bg-white/30 rounded w-24"></div>
          <div className="h-6 bg-white/40 rounded w-36"></div>
        </div>
        <div className="w-12 h-12 bg-white/20 rounded-2xl"></div>
      </div>

      {/* Comparison Cards Skeleton */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div className="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-2.5">
          <div className="flex justify-between pb-2 border-b border-slate-100">
            <div className="h-4 bg-slate-200 rounded w-28"></div>
            <div className="h-4 bg-slate-300 rounded w-20"></div>
          </div>
          {[1, 2, 3, 4].map((i) => (
            <div key={i} className="flex justify-between">
              <div className="h-3 bg-slate-100 rounded w-28"></div>
              <div className="h-3 bg-slate-200 rounded w-20"></div>
            </div>
          ))}
        </div>

        <div className="bg-emerald-50/50 rounded-2xl p-4 shadow-sm border border-emerald-200/60 space-y-2.5">
          <div className="flex justify-between pb-2 border-b border-emerald-100">
            <div className="h-4 bg-emerald-200 rounded w-32"></div>
            <div className="h-4 bg-emerald-300 rounded w-24"></div>
          </div>
          {[1, 2, 3, 4].map((i) => (
            <div key={i} className="flex justify-between">
              <div className="h-3 bg-emerald-100 rounded w-28"></div>
              <div className="h-3 bg-emerald-200 rounded w-20"></div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

export function NjkbListSkeleton() {
  return (
    <div className="space-y-2.5 animate-pulse">
      {[1, 2, 3, 4, 5].map((i) => (
        <div
          key={i}
          className="bg-white rounded-xl p-3.5 shadow-sm border border-slate-100 flex items-center justify-between"
        >
          <div className="space-y-1.5 w-2/3">
            <div className="h-4 bg-slate-200 rounded w-44"></div>
            <div className="h-3 bg-slate-100 rounded w-28"></div>
            <div className="h-4 bg-emerald-100 rounded w-24 mt-1"></div>
          </div>
          <div className="h-8 bg-slate-100 rounded-xl w-20"></div>
        </div>
      ))}
    </div>
  );
}

